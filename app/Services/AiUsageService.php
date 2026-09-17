<?php

namespace App\Services;

use App\Exceptions\RagException;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    /** Count each outgoing API attempt, including retries, under a locked daily budget. */
    public function reserve(): void
    {
        DB::transaction(function () {
            $budgets = ['global' => (int) config('rag.daily_global_limit', 2000)];
            if (auth()->id()) {
                $budgets['user:'.auth()->id()] = (int) config('rag.daily_user_limit', 100);
            }
            foreach ($budgets as $bucket => $limit) {
                $key = ['bucket' => $bucket, 'day' => now()->toDateString()];
                DB::table('ai_usage')->insertOrIgnore($key + ['requests' => 0]);
                $row = DB::table('ai_usage')->where($key)->lockForUpdate()->first();
                if ($limit > 0 && $row->requests >= $limit) {
                    throw new RagException('Batas penggunaan AI harian tercapai. Silakan coba besok atau hubungi pengelola.');
                }
                DB::table('ai_usage')->where($key)->increment('requests');
            }
        });
    }
}
