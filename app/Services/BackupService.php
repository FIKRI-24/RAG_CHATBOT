<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupService
{
    private function pg(string $tool, array $arguments, ?string $database = null): void
    {
        $config = DB::connection()->getConfig();
        $bin = rtrim((string) config('backup.pg_bin'), '/\\');
        $executable = ($bin ? $bin.DIRECTORY_SEPARATOR : '').$tool.(PHP_OS_FAMILY === 'Windows' ? '.exe' : '');
        $process = new Process(array_merge([$executable, '-h', (string) ($config['host'] ?? '127.0.0.1'), '-p', (string) ($config['port'] ?? 5432), '-U', (string) $config['username'], '-d', $database ?? $config['database']], $arguments), null, ['PGPASSWORD' => (string) ($config['password'] ?? ''), 'PGCONNECT_TIMEOUT' => '10']);
        $process->setTimeout((int) config('backup.process_timeout', 180));
        if ($process->run() !== 0) {
            // stderr may contain connection details; keep credentials and raw process output private.
            throw new RuntimeException($tool.' gagal. Periksa PG_BIN, versi PostgreSQL dan izin database.');
        }
    }

    public function create(): string
    {
        if (! app()->isDownForMaintenance()) {
            throw new RuntimeException('Jalankan php artisan down dan hentikan worker sebelum backup, lalu php artisan up setelah selesai.');
        }
        if (DB::table('jobs')->whereNotNull('reserved_at')->exists()) {
            throw new RuntimeException('Masih ada pekerjaan yang direservasi. Tunggu selesai dan hentikan worker sebelum backup.');
        }
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
            throw new RuntimeException('Backup mendukung PostgreSQL dan SQLite.');
        }
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory, 0700);
        $id = now()->format('Ymd-His').'-'.bin2hex(random_bytes(4));
        $work = $directory.'/'.$id;
        File::makeDirectory($work, 0700);
        $dump = $work.'/database.'.($driver === 'pgsql' ? 'dump' : 'sqlite');
        $archive = $directory.'/'.$id.'.zip';
        $zip = new ZipArchive;
        $opened = false;
        try {
            if ($driver === 'pgsql') {
                $this->pg('pg_dump', ['--format=custom', '--no-owner', '--no-acl', '--file='.$dump]);
            } else {
                DB::connection()->getPdo()->exec('VACUUM INTO '.DB::connection()->getPdo()->quote($dump));
            }
            if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new RuntimeException('Arsip backup tidak dapat dibuat.');
            }
            $opened = true;
            $manifest = ['version' => 1, 'driver' => $driver, 'created_at' => now()->toIso8601String(), 'counts' => [], 'files' => []];
            foreach (['users', 'modules', 'module_chunks', 'chat_histories', 'audit_events', 'developer_profiles', 'ai_usage', 'module_quizzes', 'module_quiz_attempts'] as $table) {
                if (Schema::hasTable($table)) {
                    $manifest['counts'][$table] = DB::table($table)->count();
                }
            }
            $entry = 'database/'.basename($dump);
            $zip->addFile($dump, $entry);
            $manifest['files'][$entry] = hash_file('sha256', $dump);
            $paths = ['local' => Module::pluck('file_path')->filter()->unique()->all(), 'public' => User::whereNotNull('avatar')->pluck('avatar')->unique()->all()];
            if (Schema::hasTable('developer_profiles')) {
                $paths['public'] = array_merge($paths['public'], DB::table('developer_profiles')->whereNotNull('foto')->pluck('foto')->all());
            }
            // Include retained originals and avatars, even when temporarily unreferenced.
            foreach (['local' => 'modules', 'public' => 'avatars'] as $disk => $folder) {
                $paths[$disk] = array_unique(array_merge($paths[$disk], Storage::disk($disk)->allFiles($folder)));
            }
            $paths['public'] = array_unique(array_merge($paths['public'], Storage::disk('public')->allFiles('pengembang')));
            foreach ($paths as $disk => $files) {
                $base = realpath(Storage::disk($disk)->path(''));
                foreach ($files as $file) {
                    $source = realpath(Storage::disk($disk)->path($file));
                    if (! $source || ! $base || ! str_starts_with($source, $base.DIRECTORY_SEPARATOR) || ! is_file($source)) {
                        throw new RuntimeException('Berkas yang dirujuk tidak tersedia atau berada di luar disk. Backup dibatalkan.');
                    }
                    $entry = 'files/'.$disk.'/'.str_replace('\\', '/', $file);
                    $zip->addFile($source, $entry);
                    $manifest['files'][$entry] = hash_file('sha256', $source);
                }
            }
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            if (! $zip->close()) {
                throw new RuntimeException('Penulisan backup gagal.');
            }
            $opened = false;
            chmod($archive, 0600);

            return $archive;
        } catch (\Throwable $e) {
            if ($opened) {
                $zip->close();
            }
            File::delete($archive);
            throw $e;
        } finally {
            // Only the newly-created, fixed child directory is removed.
            File::deleteDirectory($work);
        }
    }

    /** Restore into a fresh temporary database, compare row counts, and verify every file hash. */
    public function verify(string $archive): array
    {
        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('Arsip tidak dapat dibuka.');
        }
        $scratch = storage_path('app/backups/verify-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($scratch, 0700);
        $temporaryDatabase = null;
        try {
            $manifest = json_decode($zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            if (($manifest['version'] ?? null) !== 1 || ! in_array($manifest['driver'] ?? null, ['pgsql', 'sqlite'], true)) {
                throw new RuntimeException('Manifest tidak didukung.');
            }
            foreach ($manifest['files'] as $entry => $expected) {
                $stream = $zip->getStream($entry);
                if (! $stream) {
                    throw new RuntimeException('Berkas arsip hilang.');
                }
                $hash = hash_init('sha256');
                hash_update_stream($hash, $stream);
                fclose($stream);
                if (! hash_equals($expected, hash_final($hash))) {
                    throw new RuntimeException('Checksum backup tidak cocok.');
                }
            }
            $driver = $manifest['driver'];
            $dump = $scratch.'/database.'.($driver === 'pgsql' ? 'dump' : 'sqlite');
            $stream = $zip->getStream('database/'.basename($dump));
            $target = fopen($dump, 'wb');
            stream_copy_to_stream($stream, $target);
            fclose($stream);
            fclose($target);
            if ($driver === 'pgsql') {
                if (DB::connection()->getDriverName() !== 'pgsql') {
                    throw new RuntimeException('Verifikasi dump PostgreSQL memerlukan koneksi PostgreSQL.');
                }
                $temporaryDatabase = 'rag_restore_'.bin2hex(random_bytes(8));
                DB::connection()->getPdo()->exec('CREATE DATABASE "'.$temporaryDatabase.'"');
                $this->pg('pg_restore', ['--no-owner', '--no-acl', '--exit-on-error', $dump], $temporaryDatabase);
                $config = DB::connection()->getConfig();
                $config['database'] = $temporaryDatabase;
                $config['url'] = null;
                config(['database.connections.backup_verify' => $config]);
                $restored = DB::connection('backup_verify');
                foreach ($manifest['counts'] as $table => $expected) {
                    if ($restored->table($table)->count() !== $expected) {
                        throw new RuntimeException('Jumlah baris hasil restore tidak cocok.');
                    }
                }
                DB::purge('backup_verify');
            } else {
                $pdo = new \PDO('sqlite:'.$dump);
                if ($pdo->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
                    throw new RuntimeException('Integritas SQLite gagal.');
                }
                foreach ($manifest['counts'] as $table => $expected) {
                    if (! preg_match('/^[a-z_]+$/', $table) || (int) $pdo->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn() !== $expected) {
                        throw new RuntimeException('Jumlah baris hasil restore tidak cocok.');
                    }
                }
                $pdo = null;
            }

            return ['restored' => true, 'driver' => $driver, 'counts' => $manifest['counts'], 'verified_files' => count($manifest['files'])];
        } finally {
            DB::purge('backup_verify');
            if ($temporaryDatabase !== null) {
                DB::connection()->getPdo()->exec('DROP DATABASE IF EXISTS "'.$temporaryDatabase.'"');
            }
            $zip->close();
            File::deleteDirectory($scratch);
        }
    }
}
