<?php

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    private BackupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BackupService::class);
    }

    /**
     * 1. driver mysql dan mariadb tidak lagi ditolak pada validasi driver backup.
     */
    public function test_mysql_and_mariadb_driver_not_rejected_in_driver_validation(): void
    {
        $this->artisan('down');

        try {
            $mockConnection = \Mockery::mock(DB::connection());
            $mockConnection->shouldReceive('getDriverName')->andReturn('mysql');
            $mockConnection->shouldReceive('getConfig')->andReturn([
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'root',
                'password' => '',
                'database' => 'test_db',
            ]);

            DB::shouldReceive('connection')->andReturn($mockConnection);
            DB::shouldReceive('table')->with('jobs')->andReturnSelf();
            DB::shouldReceive('whereNotNull')->with('reserved_at')->andReturnSelf();
            DB::shouldReceive('exists')->andReturn(false);

            config(['backup.mysql_bin' => '']);

            try {
                $this->service->create();
                $this->fail('Expected RuntimeException for missing MYSQL_BIN.');
            } catch (RuntimeException $e) {
                $this->assertStringNotContainsString('Backup mendukung PostgreSQL dan SQLite', $e->getMessage());
                $this->assertStringContainsString('MYSQL_BIN', $e->getMessage());
            }
        } finally {
            $this->artisan('up');
            \Mockery::close();
        }
    }

    /**
     * 1b. driver mysql dan mariadb tidak ditolak pada manifest verification.
     */
    public function test_mysql_driver_in_manifest_is_recognized_by_verify(): void
    {
        $zipPath = storage_path('app/backups/test_manifest_mysql.zip');
        File::ensureDirectoryExists(dirname($zipPath));
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $manifest = [
            'version' => 1,
            'driver' => 'mysql',
            'created_at' => now()->toIso8601String(),
            'counts' => [],
            'files' => [],
        ];
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->addFromString('database/database.sql', '-- dummy sql');
        $zip->close();

        try {
            if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $this->service->verify($zipPath);
                $this->fail('Expected connection mismatch exception');
            } else {
                $result = $this->service->verify($zipPath);
                $this->assertTrue($result['restored']);
                $this->assertSame('mysql', $result['driver']);
            }
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString('Manifest tidak didukung', $e->getMessage());
            $this->assertStringContainsString('Verifikasi dump MySQL/MariaDB memerlukan koneksi MySQL/MariaDB', $e->getMessage());
        } finally {
            File::delete($zipPath);
        }
    }

    /**
     * 2. MYSQL_BIN kosong atau salah menghasilkan error yang jelas.
     */
    public function test_mysql_bin_empty_throws_clear_error(): void
    {
        config(['backup.mysql_bin' => '']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Konfigurasi MYSQL_BIN tidak ditemukan atau kosong. Periksa konfigurasi MYSQL_BIN di file .env.');

        $this->service->getMysqlBinary('mysqldump');
    }

    public function test_mysql_bin_invalid_folder_throws_clear_error(): void
    {
        $invalidDir = 'C:/non_existent_folder_'.bin2hex(random_bytes(4));
        config(['backup.mysql_bin' => $invalidDir]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tool mysqldump tidak ditemukan');

        $this->service->getMysqlBinary('mysqldump');
    }

    public function test_mysql_binary_mysql_tool_resolution_throws_clear_error_when_invalid(): void
    {
        $invalidDir = 'C:/non_existent_folder_'.bin2hex(random_bytes(4));
        config(['backup.mysql_bin' => $invalidDir]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tool mysql tidak ditemukan');

        $this->service->getMysqlBinary('mysql');
    }

    /**
     * 3. Password tidak pernah muncul di argumen baris perintah proses, tapi diteruskan via MYSQL_PWD.
     */
    public function test_password_is_never_passed_in_process_arguments(): void
    {
        $tempDir = storage_path('app/backups/test_bin');
        File::ensureDirectoryExists($tempDir);
        $ext = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $dumpExe = $tempDir.'/mysqldump'.$ext;
        $mysqlExe = $tempDir.'/mysql'.$ext;
        file_put_contents($dumpExe, '');
        file_put_contents($mysqlExe, '');

        try {
            config(['backup.mysql_bin' => $tempDir]);

            $secretPassword = 'super_secret_db_password_!@#$%^';
            $config = [
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'test_user',
                'password' => $secretPassword,
                'database' => 'test_db',
            ];

            // Test mysqldump process
            $dumpProcess = $this->service->buildMysqlDumpProcess('/path/to/dump.sql', $config);
            $dumpCmd = $dumpProcess->getCommandLine();

            $this->assertStringNotContainsString($secretPassword, $dumpCmd);
            $this->assertStringContainsString('--single-transaction', $dumpCmd);
            $this->assertStringContainsString('--default-character-set=utf8mb4', $dumpCmd);
            $this->assertStringContainsString('--routines', $dumpCmd);
            $this->assertStringContainsString('--no-tablespaces', $dumpCmd);
            $this->assertStringContainsString('--max-allowed-packet=256M', $dumpCmd);
            $this->assertStringContainsString('--skip-extended-insert', $dumpCmd);
            $this->assertStringContainsString('--result-file=/path/to/dump.sql', $dumpCmd);
            $this->assertEquals($secretPassword, $dumpProcess->getEnv()['MYSQL_PWD'] ?? null);

            // Test mysql restore process
            $restoreProcess = $this->service->buildMysqlRestoreProcess('rag_restore_drill_tmp', $config);
            $restoreCmd = $restoreProcess->getCommandLine();

            $this->assertStringNotContainsString($secretPassword, $restoreCmd);
            $this->assertStringContainsString('--default-character-set=utf8mb4', $restoreCmd);
            $this->assertStringContainsString('--max-allowed-packet=256M', $restoreCmd);
            $this->assertStringContainsString('rag_restore_drill_tmp', $restoreCmd);
            $this->assertEquals($secretPassword, $restoreProcess->getEnv()['MYSQL_PWD'] ?? null);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    /**
     * 4. Guard nama database drill menolak database aktif/produksi dan pola nama berbahaya.
     */
    public function test_safe_drill_database_guard_rejects_active_database_name(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak aman atau sama dengan database aktif');

        $this->service->assertSafeDrillDatabase('rag_chatbot', 'rag_chatbot');
    }

    public function test_safe_drill_database_guard_rejects_names_without_prefix(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak aman atau sama dengan database aktif');

        $this->service->assertSafeDrillDatabase('my_production_db', 'active_db');
    }

    public function test_safe_drill_database_guard_rejects_unsafe_characters(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak aman atau sama dengan database aktif');

        $this->service->assertSafeDrillDatabase('rag_restore_drill_`test`;DROP TABLE', 'active_db');
    }

    public function test_safe_drill_database_guard_accepts_valid_drill_name(): void
    {
        $this->service->assertSafeDrillDatabase('rag_restore_drill_a1b2c3d4', 'rag_chatbot');
        $this->assertTrue(true);
    }

    /**
     * 5. Tes live mysql binary jika tersedia, dilewati jika mysqldump tidak ada.
     */
    public function test_live_mysql_binary_execution(): void
    {
        try {
            $binary = $this->service->getMysqlBinary('mysqldump');
        } catch (RuntimeException $e) {
            $this->markTestSkipped('mysqldump binary tidak tersedia: '.$e->getMessage());
        }

        $process = new Process([$binary, '--version']);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            $this->markTestSkipped('mysqldump binary tidak dapat dijalankan.');
        }

        $this->assertStringContainsString('mysqldump', strtolower($process->getOutput()));
    }
}
