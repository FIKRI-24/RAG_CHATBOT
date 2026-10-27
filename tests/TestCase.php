<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstTestingOnAppDatabase();
    }

    /**
     * Guard safety check: prevent tests from ever running against the application/production database.
     */
    protected function guardAgainstTestingOnAppDatabase(): void
    {
        $connection = config('database.default');
        $activeTestDb = (string) config("database.connections.{$connection}.database", '');

        $appDb = 'rag_chatbot';
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, 'DB_DATABASE=')) {
                    $parts = explode('=', $trimmed, 2);
                    $val = trim($parts[1] ?? '', " \t\n\r\0\x0B\"'");
                    if ($val !== '') {
                        $appDb = $val;
                    }
                    break;
                }
            }
        }

        if (in_array($connection, ['mysql', 'mariadb', 'pgsql'], true)) {
            if ($activeTestDb === '' || $activeTestDb === $appDb || $activeTestDb === 'rag_chatbot' || ! str_contains($activeTestDb, 'test')) {
                throw new RuntimeException("Tes ditolak demi keamanan: Database pengujian ('{$activeTestDb}') tidak boleh sama dengan database utama aplikasi ('{$appDb}') dan harus beridentitas database uji.");
            }
        }
    }
}
