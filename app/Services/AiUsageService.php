<?php

namespace App\Services;

use App\Exceptions\RagException;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    /** Count each logical outgoing API operation under a daily WIB school budget. */
    public function reserve(string $scope = 'chat'): void
    {
        DB::transaction(function () use ($scope) {
            $day = now(config('app.display_timezone', 'Asia/Jakarta'))->toDateString();

            if ($scope === 'indexing') {
                $this->consumeBudget(
                    'indexing',
                    $day,
                    (int) config('rag.daily_indexing_limit', 1000),
                    'Batas penggunaan AI indexing harian tercapai. Silakan coba besok atau hubungi pengelola.'
                );

                return;
            }

            $budgets = ['global' => (int) config('rag.daily_global_limit', 2000)];
            if ($userId = auth()->id()) {
                $budgets['user:'.$userId] = (int) config('rag.daily_user_limit', 100);
            }

            foreach ($budgets as $bucket => $limit) {
                $this->consumeBudget($bucket, $day, $limit);
            }
        }, 3);
    }

    private function consumeBudget(
        string $bucket,
        string $day,
        int $limit,
        string $exhaustedMessage = 'Batas penggunaan AI harian tercapai. Silakan coba besok atau hubungi pengelola.'
    ): void {
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
                throw RagException::permanent($exhaustedMessage);
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
