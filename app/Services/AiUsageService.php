<?php

namespace App\Services;

use App\Exceptions\RagException;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    /** Count each outgoing API attempt, including retries, under a daily budget. */
    public function reserve(): void
    {
        DB::transaction(function () {
            $day = now()->toDateString();
            $budgets = ['global' => (int) config('rag.daily_global_limit', 2000)];
            if ($userId = auth()->id()) {
                $budgets['user:'.$userId] = (int) config('rag.daily_user_limit', 100);
            }

            foreach ($budgets as $bucket => $limit) {
                $this->consumeBudget($bucket, $day, $limit);
            }
        }, 3);
    }

    private function consumeBudget(string $bucket, string $day, int $limit): void
    {
        $affected = $this->incrementBucket($bucket, $day, $limit);

        if ($affected === 0) {
            if (! DB::table('ai_usage')->where('bucket', $bucket)->where('day', $day)->exists()) {
                DB::table('ai_usage')->insertOrIgnore([
                    'bucket' => $bucket,
                    'day' => $day,
                    'requests' => 0,
                ]);

                $affected = $this->incrementBucket($bucket, $day, $limit);
            }

            if ($affected === 0) {
                throw new RagException('Batas penggunaan AI harian tercapai. Silakan coba besok atau hubungi pengelola.');
            }
        }
    }

    private function incrementBucket(string $bucket, string $day, int $limit): int
    {
        $query = DB::table('ai_usage')
            ->where('bucket', $bucket)
            ->where('day', $day);

        if ($limit > 0) {
            $query->where('requests', '<', $limit);
        }

        return $query->increment('requests');
    }
}
