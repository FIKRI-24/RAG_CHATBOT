<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemStatusService
{
    public function snapshot(): array
    {
        $oldest = DB::table('jobs')->where('queue', 'rag')->min('created_at');
        $heartbeat = Cache::get('rag-worker:heartbeat');

        return [
            'database' => DB::connection()->getDriverName(),
            'pending_jobs' => DB::table('jobs')->where('queue', 'rag')->count(),
            'oldest_job_age_seconds' => $oldest ? max(0, time() - $oldest) : 0,
            'failed_jobs' => DB::table('failed_jobs')->where('queue', 'rag')->count(),
            'failed_modules' => Module::where('status_indexing', 'failed')->count(),
            'worker_heartbeat' => $heartbeat,
            'worker_observed' => $heartbeat !== null && time() - $heartbeat < 390,
            'ai_configured' => (string) config('gemini.api_key') !== '',
            'api_attempts_today' => DB::table('ai_usage')->where('bucket', 'global')->whereDate('day', today())->value('requests') ?? 0,
            'registration_enabled' => (bool) config('security.registration_enabled'),
            'checked_at' => now(config('app.display_timezone'))->toIso8601String(),
        ];
    }
}
