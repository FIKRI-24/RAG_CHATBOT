<?php

namespace App\Console\Commands;

use App\Services\SystemStatusService;
use Illuminate\Console\Command;

class SystemCheck extends Command
{
    protected $signature = 'system:check {--strict : Fail when worker, AI or queues need attention}';

    protected $description = 'Read database, RAG queue and AI configuration health without an external API call.';

    public function handle(SystemStatusService $service): int
    {
        try {
            $status = $service->snapshot();
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $this->option('strict') && (! $status['worker_observed'] || ! $status['ai_configured']
                || $status['failed_jobs'] > 0 || $status['failed_modules'] > 0 || $status['oldest_job_age_seconds'] > 600)
                ? self::FAILURE : self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Pemeriksaan gagal: database atau cache tidak tersedia.');

            return self::FAILURE;
        }
    }
}
