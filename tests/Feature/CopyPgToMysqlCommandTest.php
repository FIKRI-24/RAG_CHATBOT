<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CopyPgToMysqlCommandTest extends TestCase
{
    use RefreshDatabase;
    public function test_command_signature_and_description_are_registered(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('data:copy-pg-to-mysql', $commands);

        $command = $commands['data:copy-pg-to-mysql'];
        $this->assertStringContainsString('data:copy-pg-to-mysql', $command->getName());
        $this->assertStringContainsString('pgsql_legacy', $command->getDescription());
    }

    public function test_command_rejects_running_on_non_empty_database_without_force(): void
    {
        if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') === ':memory:') {
            $this->markTestSkipped('Uji coba migrasi pgsql -> mysql memerlukan koneksi MySQL aktif.');
        }

        // Pastikan ada satu baris di users pada database uji
        $userId = DB::table('users')->insertGetId([
            'name' => 'Existing User',
            'email' => 'guard_test@example.com',
            'password' => 'secret',
            'role' => 'siswa',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Panggil command tanpa --force, harus gagal (exit code 1)
            $exitCode = Artisan::call('data:copy-pg-to-mysql');
            $output = Artisan::output();

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('Tolak jalan', $output);
            $this->assertStringContainsString('--force', $output);
        } finally {
            DB::table('users')->where('id', $userId)->delete();
        }
    }
}
