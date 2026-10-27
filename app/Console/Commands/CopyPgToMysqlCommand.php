<?php

/**
 * =========================================================================
 * PERINTAH SEMENTARA: SALIN DATA DARI POSTGRESQL KE MYSQL / MARIADB
 * =========================================================================
 * File ini dibuat secara khusus dan sementara untuk memigrasikan seluruh
 * data operasional dari database lama (PostgreSQL / pgsql_legacy) ke database
 * baru (MySQL / mysql).
 *
 * Jaminan Keamanan & Aturan:
 * 1. Sumber pgsql_legacy bersifat HANYA BACA (read-only) dan dikunci dengan
 *    'SET default_transaction_read_only = on;'. Tidak pernah menulis ke sumber.
 * 2. Tujuan harus database kosong hasil migrate. Ditolak jika tabel sudah
 *    berisi data, kecuali dengan flag eksplisit --force.
 * 3. Salin dilakukan per tabel dengan urutan induk ke anak, chunk 200 baris,
 *    mempertahankan id asli, dan mematikan FOREIGN_KEY_CHECKS sementara.
 * 4. Tabel sistem dilewati: migrations, sessions, cache, cache_locks, jobs, failed_jobs, job_batches.
 * 5. Kolom JSON & embedding_vector disalin apa adanya (string) tanpa decode/encode ulang.
 * 6. Membandingkan jumlah baris dan checksum kolom kunci, serta mengatur ulang AUTO_INCREMENT.
 *
 * File ini DAPAT DIHAPUS DENGAN AMAN setelah proses migrasi data selesai.
 * =========================================================================
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class CopyPgToMysqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:copy-pg-to-mysql
                            {--force : Paksa salin data meskipun tabel tujuan sudah berisi baris data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[SEMENTARA] Salin data dari PostgreSQL (pgsql_legacy) ke MySQL (mysql)';

    /**
     * Urutan tabel dari induk ke anak.
     *
     * @var array<int, string>
     */
    protected array $tables = [
        'users',
        'developer_profiles',
        'password_reset_tokens',
        'modules',
        'module_chunks',
        'module_quizzes',
        'module_quiz_attempts',
        'chat_histories',
        'audit_events',
        'ai_usage',
    ];

    /**
     * Kolom kunci untuk verifikasi integritas checksum per tabel.
     *
     * @var array<string, array<int, string>>
     */
    protected array $keyColumns = [
        'users' => ['id', 'email', 'role', 'is_active', 'name', 'class_name'],
        'developer_profiles' => ['id', 'nama', 'nim', 'email'],
        'password_reset_tokens' => ['email', 'token'],
        'modules' => ['id', 'judul', 'mapel', 'status_indexing', 'kb_nomor'],
        'module_chunks' => ['id', 'module_id', 'chunk_index', 'embedding_dimensions', 'chunk_text', 'embedding_vector'],
        'module_quizzes' => ['id', 'module_id', 'title', 'is_published', 'questions'],
        'module_quiz_attempts' => ['id', 'module_quiz_id', 'user_id', 'score', 'correct_count', 'answers', 'questions_snapshot'],
        'chat_histories' => ['id', 'siswa_id', 'mapel', 'kind', 'quiz_status', 'score', 'pertanyaan', 'jawaban', 'sources', 'quiz_payload', 'assessment'],
        'audit_events' => ['id', 'actor_id', 'action', 'subject_type', 'subject_id', 'metadata'],
        'ai_usage' => ['id', 'bucket', 'day', 'requests'],
    ];

    /**
     * Daftar nama kolom JSON yang memerlukan normalisasi representasi spasi/urutan kunci saat checksum.
     *
     * @var array<int, string>
     */
    protected array $jsonColumns = [
        'questions',
        'questions_snapshot',
        'answers',
        'metadata',
        'sources',
        'quiz_payload',
        'assessment',
        'additional_videos',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  MIGRASI DATA SEMENTARA: PostgreSQL (pgsql_legacy) -> MySQL   ');
        $this->info('================================================================');

        $sourceConnName = 'pgsql_legacy';
        $targetConnName = 'mysql';

        // 1. Validasi konfigurasi koneksi
        $this->ensureSourceConnectionConfigured($sourceConnName);

        try {
            $source = DB::connection($sourceConnName);
            $target = DB::connection($targetConnName);

            // Verifikasi koneksi dan kunci sumber ke READ ONLY
            $source->getPdo();
            $source->statement('SET default_transaction_read_only = on;');
            $this->info("Koneksi sumber [{$sourceConnName}] terhubung dan dikunci READ ONLY.");

            $targetDriver = $target->getDriverName();
            if (! in_array($targetDriver, ['mysql', 'mariadb'], true)) {
                $this->error("Koneksi tujuan [{$targetConnName}] harus driver mysql atau mariadb (terdeteksi: {$targetDriver}).");
                return self::FAILURE;
            }
            $target->getPdo();
            $this->info("Koneksi tujuan [{$targetConnName}] terhubung (driver: {$targetDriver}, db: {$target->getDatabaseName()}).");

            // 2. Periksa skema tabel tujuan dan pastikan tujuan kosong (kecuali --force)
            $this->checkTargetSchemaAndEmptiness($targetConnName);

            // 3. Proses penyalinan data per tabel
            $this->info("\nMemulai proses penyalinan data...");
            $target->statement('SET FOREIGN_KEY_CHECKS=0;');

            try {
                if ($this->option('force')) {
                    $this->warn('Opsi --force aktif: Membersihkan tabel tujuan sebelum pengisian ulang...');
                    // Bersihkan dari anak ke induk jika force
                    foreach (array_reverse($this->tables) as $table) {
                        $target->table($table)->truncate();
                    }
                }

                foreach ($this->tables as $table) {
                    $this->copyTable($source, $target, $table);
                }
            } finally {
                $target->statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->info('FOREIGN_KEY_CHECKS diaktifkan kembali pada database tujuan.');
            }

            // 4. Atur ulang AUTO_INCREMENT pada tabel tujuan
            $this->info("\nMenyesuaikan nilai AUTO_INCREMENT...");
            $this->resetAutoIncrements($target);

            // 5. Bandingkan jumlah baris dan checksum
            $this->info("\nMelakukan verifikasi jumlah baris dan checksum data...");
            $verificationPassed = $this->verifyDataIntegrity($source, $target);

            if (! $verificationPassed) {
                $this->error("\nVerifikasi integritas data GAGAL! Periksa tabel laporan di atas.");
                return self::FAILURE;
            }

            $this->info("\n================================================================");
            $this->info('  MIGRASI DATA SELESAI: 100% SUKSES DAN DATA COCOK SEMPURNA     ');
            $this->info("================================================================\n");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Terjadi kesalahan fatal selama proses migrasi: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    /**
     * Pastikan konfigurasi koneksi pgsql_legacy terdaftar.
     */
    protected function ensureSourceConnectionConfigured(string $name): void
    {
        if (! config()->has("database.connections.{$name}")) {
            config(["database.connections.{$name}" => [
                'driver' => 'pgsql',
                'host' => env('PGSQL_HOST', '127.0.0.1'),
                'port' => env('PGSQL_PORT', '5432'),
                'database' => env('PGSQL_DATABASE', 'rag_chatbot'),
                'username' => env('PGSQL_USERNAME', 'postgres'),
                'password' => env('PGSQL_PASSWORD', ''),
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => 'public',
                'sslmode' => 'prefer',
            ]]);
        }
    }

    /**
     * Periksa apakah tabel tujuan sudah siap dan kosong.
     */
    protected function checkTargetSchemaAndEmptiness(string $targetConn): void
    {
        $target = DB::connection($targetConn);
        $nonEmptyTables = [];

        foreach ($this->tables as $table) {
            if (! Schema::connection($targetConn)->hasTable($table)) {
                throw new RuntimeException("Tabel tujuan '{$table}' belum ada pada database MySQL. Silakan jalankan 'php artisan migrate' terlebih dahulu.");
            }

            $count = $target->table($table)->count();
            if ($count > 0) {
                $nonEmptyTables[$table] = $count;
            }
        }

        if (! empty($nonEmptyTables) && ! $this->option('force')) {
            $tableList = implode(', ', array_map(fn($t, $c) => "{$t} ({$c} baris)", array_keys($nonEmptyTables), $nonEmptyTables));
            throw new RuntimeException(
                "Tolak jalan: Database tujuan sudah berisi data pada tabel: {$tableList}.\n" .
                "Gunakan opsi '--force' jika Anda benar-benar yakin ingin membersihkan dan menimpa data."
            );
        }
    }

    /**
     * Salin satu tabel secara berurutan dalam chunk 200 baris.
     * Kolom JSON dan embedding_vector disalin apa adanya tanpa decode/encode ulang.
     */
    protected function copyTable($source, $target, string $table): void
    {
        $orderCol = Schema::connection('mysql')->hasColumn($table, 'id') ? 'id' : 'email';

        $totalSource = $source->table($table)->count();
        $this->line("Menyalin tabel [<comment>{$table}</comment>]: {$totalSource} baris...");

        if ($totalSource === 0) {
            return;
        }

        $chunkSize = 200;
        $offset = 0;

        while ($offset < $totalSource) {
            $rows = $source->table($table)
                ->orderBy($orderCol)
                ->offset($offset)
                ->limit($chunkSize)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $batch = [];
            foreach ($rows as $row) {
                $rowArr = (array) $row;
                // Pastikan tipe boolean dikonversi menjadi 1/0 untuk tinyint MySQL
                foreach ($rowArr as $key => $val) {
                    if (is_bool($val)) {
                        $rowArr[$key] = $val ? 1 : 0;
                    }
                }
                $batch[] = $rowArr;
            }

            $target->table($table)->insert($batch);
            $offset += count($batch);
        }
    }

    /**
     * Atur ulang AUTO_INCREMENT pada semua tabel tujuan ke MAX(id)+1.
     */
    protected function resetAutoIncrements($target): void
    {
        foreach ($this->tables as $table) {
            if (Schema::connection('mysql')->hasColumn($table, 'id')) {
                $maxId = $target->table($table)->max('id');
                $nextId = ($maxId !== null) ? ((int) $maxId + 1) : 1;
                $target->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId};");
                $this->line("  - {$table}: AUTO_INCREMENT diatur ke <info>{$nextId}</info>");
            }
        }
    }

    /**
     * Bandingkan jumlah baris dan checksum beberapa kolom kunci antara sumber dan tujuan.
     */
    protected function verifyDataIntegrity($source, $target): bool
    {
        $allPassed = true;
        $report = [];

        foreach ($this->tables as $table) {
            $sourceCount = $source->table($table)->count();
            $targetCount = $target->table($table)->count();
            $diff = $targetCount - $sourceCount;

            $sourceHash = $this->calculateTableChecksum($source, $table);
            $targetHash = $this->calculateTableChecksum($target, $table);

            $checksumMatch = ($sourceHash === $targetHash);

            if ($diff !== 0 || ! $checksumMatch) {
                $allPassed = false;
                $status = '<error>TIDAK COCOK</error>';
            } else {
                $status = '<info>COCOK (0)</info>';
            }

            $report[] = [
                'table' => $table,
                'source_rows' => $sourceCount,
                'target_rows' => $targetCount,
                'diff' => $diff,
                'checksum' => $checksumMatch ? '<info>IDENTIK</info>' : '<error>BEDA</error>',
                'status' => $status,
            ];
        }

        $this->table(
            ['Tabel', 'Sumber (PG)', 'Tujuan (MySQL)', 'Selisih', 'Checksum Kunci', 'Status'],
            $report
        );

        return $allPassed;
    }

    /**
     * Hitung checksum SHA-256 dari kolom kunci seluruh baris tabel secara terurut.
     */
    protected function calculateTableChecksum($conn, string $table): string
    {
        $orderCol = Schema::connection('mysql')->hasColumn($table, 'id') ? 'id' : 'email';
        $keyCols = $this->keyColumns[$table] ?? [$orderCol];

        $hashContext = hash_init('sha256');

        $chunkSize = 200;
        $offset = 0;
        $total = $conn->table($table)->count();

        while ($offset < $total) {
            $rows = $conn->table($table)
                ->select($keyCols)
                ->orderBy($orderCol)
                ->offset($offset)
                ->limit($chunkSize)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                $rowArr = (array) $row;
                $rowTokens = [];
                foreach ($keyCols as $col) {
                    $val = $rowArr[$col] ?? null;
                    if (is_bool($val)) {
                        $val = $val ? '1' : '0';
                    } elseif ($val === null) {
                        $val = '__NULL__';
                    } elseif (in_array($col, $this->jsonColumns, true) && is_string($val)) {
                        // Normalisasi representasi objek JSON (MySQL secara otomatis mengurutkan kunci objek secara alfabetis)
                        $decoded = json_decode($val, true);
                        if ($decoded !== null) {
                            $this->sortKeysRecursively($decoded);
                            $val = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        $val = (string) $val;
                    }
                    $rowTokens[] = $val;
                }
                hash_update($hashContext, implode('|', $rowTokens) . "\n");
            }

            $offset += $rows->count();
        }

        return hash_final($hashContext);
    }

    /**
     * Urutkan kunci asosiatif secara rekursif agar representasi JSON kanonikal identik.
     */
    protected function sortKeysRecursively(mixed &$item): void
    {
        if (! is_array($item)) {
            return;
        }

        $isAssoc = array_keys($item) !== range(0, count($item) - 1);
        if ($isAssoc) {
            ksort($item);
        }

        foreach ($item as &$child) {
            if (is_array($child)) {
                $this->sortKeysRecursively($child);
            }
        }
    }
}
