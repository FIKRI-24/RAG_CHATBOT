<?php

namespace Tests\Feature;

use App\Exceptions\RagException;
use App\Models\User;
use App\Services\AiUsageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AiUsageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * (a) Request tepat di batas diterima dan request sesudahnya ditolak.
     */
    public function test_request_at_exact_limit_is_accepted_and_subsequent_request_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($user);

        // Atur batas user = 3, global = 10
        config([
            'rag.daily_user_limit' => 3,
            'rag.daily_global_limit' => 10,
        ]);

        $service = app(AiUsageService::class);

        // Request 1, 2, dan 3 (tepat batas) harus diterima
        $service->reserve();
        $service->reserve();
        $service->reserve();

        $this->assertSame(3, (int) DB::table('ai_usage')->where('bucket', 'user:'.$user->id)->value('requests'));
        $this->assertSame(3, (int) DB::table('ai_usage')->where('bucket', 'global')->value('requests'));

        // Request ke-4 (melebihi batas) harus ditolak dengan pesan yang tepat
        $this->expectException(RagException::class);
        $this->expectExceptionMessage('Batas penggunaan AI harian tercapai. Silakan coba besok atau hubungi pengelola.');

        $service->reserve();
    }

    /**
     * (b) Rollback global saat kuota user habis.
     */
    public function test_global_increment_rolls_back_when_user_quota_exhausted(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($user);

        // Batas user = 1, global = 10
        config([
            'rag.daily_user_limit' => 1,
            'rag.daily_global_limit' => 10,
        ]);

        $service = app(AiUsageService::class);

        // Request 1 berhasil
        $service->reserve();
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'global')->value('requests'));
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'user:'.$user->id)->value('requests'));

        // Request 2 gagal karena kuota user habis
        try {
            $service->reserve();
            $this->fail('Harusnya melempar RagException karena kuota user habis');
        } catch (RagException $e) {
            $this->assertSame('Batas penggunaan AI harian tercapai. Silakan coba besok atau hubungi pengelola.', $e->getMessage());
        }

        // Kenaikan kuota global harus ikut di-rollback (tetap bernilai 1, bukan 2)
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'global')->value('requests'));
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'user:'.$user->id)->value('requests'));
    }

    /**
     * (c) Baris hari baru dibuat otomatis.
     */
    public function test_new_day_row_is_created_automatically(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($user);

        config([
            'rag.daily_user_limit' => 10,
            'rag.daily_global_limit' => 50,
        ]);

        $service = app(AiUsageService::class);

        // Hari 1: 2026-10-26
        Carbon::setTestNow(Carbon::parse('2026-10-26 09:00:00'));
        $service->reserve();

        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'global')->where('day', '2026-10-26')->value('requests'));
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'user:'.$user->id)->where('day', '2026-10-26')->value('requests'));

        // Hari 2: 2026-10-27
        Carbon::setTestNow(Carbon::parse('2026-10-27 09:00:00'));
        $service->reserve();

        // Hari baru otomatis memiliki baris sendiri
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'global')->where('day', '2026-10-27')->value('requests'));
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'user:'.$user->id)->where('day', '2026-10-27')->value('requests'));

        // Data hari kemarin tetap utuh
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'global')->where('day', '2026-10-26')->value('requests'));
    }

    /**
     * (d) Limit = 0 tidak membatasi.
     */
    public function test_limit_zero_or_negative_is_unlimited(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($user);

        // Limit 0 menandakan unlimited
        config([
            'rag.daily_user_limit' => 0,
            'rag.daily_global_limit' => 0,
        ]);

        $service = app(AiUsageService::class);

        // Jalankan reservasi 10 kali
        for ($i = 1; $i <= 10; $i++) {
            $service->reserve();
        }

        $this->assertSame(10, (int) DB::table('ai_usage')->where('bucket', 'global')->value('requests'));
        $this->assertSame(10, (int) DB::table('ai_usage')->where('bucket', 'user:'.$user->id)->value('requests'));
    }

    /**
     * (e) Bucket indexing terpisah dari kuota global dan kuota siswa.
     */
    public function test_indexing_scope_uses_isolated_bucket_and_does_not_drain_student_global_quota(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student);

        config([
            'rag.daily_user_limit' => 2,
            'rag.daily_global_limit' => 2,
            'rag.daily_indexing_limit' => 3,
        ]);

        $service = app(AiUsageService::class);

        // 3 kali indexing menghabiskan bucket 'indexing', tetapi bucket 'global' dan 'user' tetap 0
        $service->reserve('indexing');
        $service->reserve('indexing');
        $service->reserve('indexing');

        $this->assertSame(3, (int) DB::table('ai_usage')->where('bucket', 'indexing')->value('requests'));
        $this->assertNull(DB::table('ai_usage')->where('bucket', 'global')->value('requests'));
        $this->assertNull(DB::table('ai_usage')->where('bucket', 'user:'.$student->id)->value('requests'));

        // Siswa masih tetap dapat memakai chatbot secara normal
        $service->reserve('chat');
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'global')->value('requests'));
        $this->assertSame(1, (int) DB::table('ai_usage')->where('bucket', 'user:'.$student->id)->value('requests'));

        // Request indexing ke-4 ditolak secara permanen (retryable = false)
        try {
            $service->reserve('indexing');
            $this->fail('Harusnya melempar RagException karena batas indexing tercapai');
        } catch (RagException $e) {
            $this->assertFalse($e->retryable);
            $this->assertStringContainsString('indexing', $e->getMessage());
        }
    }
}
