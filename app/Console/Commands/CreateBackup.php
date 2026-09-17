<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CreateBackup extends Command
{
    protected $signature = 'backup:create {--verify : Restore into an isolated database and verify file checksums}';

    protected $description = 'Create a local database + module + avatar backup; requires maintenance mode and stopped workers.';

    public function handle(BackupService $service): int
    {
        try {
            $path = $service->create();
            $this->info('Backup: '.$path);
            if ($this->option('verify')) {
                $this->line(json_encode($service->verify($path), JSON_PRETTY_PRINT));
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e instanceof \RuntimeException ? $e->getMessage() : 'Backup atau verifikasi gagal.');

            return self::FAILURE;
        }
    }
}
