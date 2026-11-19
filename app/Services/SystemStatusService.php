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

        $todayWib = today(config('app.display_timezone', 'Asia/Jakarta'));

        return [
            'database' => DB::connection()->getDriverName(),
            'pending_jobs' => DB::table('jobs')->where('queue', 'rag')->count(),
            'oldest_job_age_seconds' => $oldest ? max(0, time() - $oldest) : 0,
            'failed_jobs' => DB::table('failed_jobs')->where('queue', 'rag')->count(),
            'failed_modules' => Module::where(function ($q) {
                $q->where('status_indexing', 'failed')->orWhere('reindexing_status', 'failed');
            })->count(),
            'worker_heartbeat' => $heartbeat,
            'worker_observed' => $heartbeat !== null && time() - $heartbeat < 390,
            'ai_configured' => (string) config('gemini.api_key') !== '',
            'api_attempts_today' => DB::table('ai_usage')->where('bucket', 'global')->whereDate('day', $todayWib)->value('requests') ?? 0,
            'indexing_attempts_today' => DB::table('ai_usage')->where('bucket', 'indexing')->whereDate('day', $todayWib)->value('requests') ?? 0,
            'registration_enabled' => (bool) config('security.registration_enabled'),
            'checked_at' => now(config('app.display_timezone'))->toIso8601String(),
        ];
    }
}
