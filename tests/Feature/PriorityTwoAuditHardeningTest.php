<?php

namespace Tests\Feature;

use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use App\Services\VectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PriorityTwoAuditHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_orphaned_attempt_from_deleted_module_blocks_other_teachers_from_idor_and_export_leak(): void
    {
        $guruA = User::factory()->create(['role' => 'guru']);
        $guruB = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_name' => 'XI TKJ 1']);

        $moduleA = Module::create([
            'guru_id' => $guruA->id,
            'judul' => 'Modul Rahasia Guru A',
            'mapel' => 'TKJ',
            'kb_nomor' => 'KB 1',
            'file_path' => 'modules/a.pdf',
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ]);

        $quiz = $moduleA->quiz()->create([
            'title' => 'Kuis Guru A',
            'is_published' => true,
            'version' => 1,
            'questions' => [[
                'text' => 'Soal 1',
                'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
                'correct_answer' => 'A',
            ]],
        ]);

        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $moduleA), [
            'quiz_id' => $quiz->id,
            'quiz_version' => 1,
            'answers' => ['B'],
        ])->assertRedirect();

        $attempt = ModuleQuizAttempt::firstOrFail();
        $this->assertSame($guruA->id, (int) $attempt->module_guru_id);

        // Delete Guru A's module -> module_id becomes NULL, but module_guru_id preserves ownership!
        $moduleA->delete();
        $attempt->refresh();
        $this->assertNull($attempt->module_id);
        $this->assertSame($guruA->id, (int) $attempt->module_guru_id);

        // Guru A (original owner) can still inspect and adjust score
        $this->actingAs($guruA)->get(route('guru.quiz-recap.show-attempt', $attempt))->assertOk();
        $this->actingAs($guruA)->patch(route('guru.quiz-recap.update-remedial-score', $attempt), [
            'score' => 80,
            'remedial_note' => 'Remedial lisan',
        ])->assertSessionHas('success');

        // Guru B MUST be blocked with 403 Forbidden (IDOR closed!)
        $this->actingAs($guruB)->get(route('guru.quiz-recap.show-attempt', $attempt))->assertForbidden();
        $this->actingAs($guruB)->patch(route('guru.quiz-recap.update-remedial-score', $attempt), [
            'score' => 100,
        ])->assertForbidden();

        // Guru B's Excel export must NOT leak Guru A's orphaned attempt
        $exportB = $this->actingAs($guruB)->get(route('guru.quiz-recap.export'));
        $exportB->assertOk();
        $tmpFile = tempnam(sys_get_temp_dir(), 'recap_b_');
        file_put_contents($tmpFile, $exportB->streamedContent());
        $sheet2 = IOFactory::load($tmpFile)->getSheet(1);
        @unlink($tmpFile);
        $text = collect($sheet2->toArray())->flatten()->filter(fn ($v) => is_string($v))->implode("\n");
        $this->assertStringNotContainsString('Modul Rahasia Guru A', $text);
    }

    public function test_student_downloads_live_indexed_file_during_pending_or_failed_reindexing(): void
    {
        Storage::fake('local');
        Queue::fake();

        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        Storage::disk('local')->put('modules/live_v1.pdf', 'LIVE-V1-PDF-CONTENT');

        // Legacy completed module with indexed_file_path initially NULL
        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Instalasi Jaringan',
            'mapel' => 'TKJ',
            'kb_nomor' => 'KB 1',
            'file_path' => 'modules/live_v1.pdf',
            'indexed_file_path' => null,
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ]);
        ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => 'Materi versi 1 yang sedang aktif di AI.',
            'embedding_vector' => [1, 0],
            'embedding_model' => GeminiService::EMBEDDING_MODEL,
            'embedding_dimensions' => 2,
            'chunk_index' => 0,
        ]);

        // Teacher uploads replacement v2 while re-indexing is still pending
        $v2File = UploadedFile::fake()->createWithContent('v2_unindexed.pdf', 'NEW-V2-UNINDEXED-CONTENT');
        $this->actingAs($guru)->put(route('guru.modules.update', $module), [
            'judul' => $module->judul,
            'mapel' => $module->mapel,
            'kb_nomor' => $module->kb_nomor,
            'file' => $v2File,
        ])->assertRedirect(route('guru.modules.index'));

        $module->refresh();
        $this->assertSame('pending', $module->reindexing_status);
        $this->assertSame('modules/live_v1.pdf', $module->indexed_file_path);
        $this->assertNotSame('modules/live_v1.pdf', $module->file_path);

        // Student MUST still download live v1 content (matching active RAG index)
        $studentDownload = $this->actingAs($siswa)->get(route('modules.download', $module));
        $studentDownload->assertOk();
        $this->assertSame('LIVE-V1-PDF-CONTENT', $studentDownload->streamedContent());

        // Teacher owner downloads latest uploaded v2 file for review
        $guruDownload = $this->actingAs($guru)->get(route('modules.download', $module));
        $guruDownload->assertOk();
        $this->assertSame('NEW-V2-UNINDEXED-CONTENT', $guruDownload->streamedContent());
    }

    public function test_vector_service_single_pass_cosine_matches_reference_and_sources_batch_eliminates_n_plus_1(): void
    {
        $query = [0.6, 0.8, 0.0];
        $cand = [0.8, 0.6, 0.0];
        $norm = VectorService::norm($query);
        $this->assertNotNull($norm);
        $this->assertEqualsWithDelta(
            VectorService::cosine($query, $cand),
            VectorService::cosineWithQueryNorm($query, $norm, $cand),
            1e-12
        );
        $this->assertNull(VectorService::cosineWithQueryNorm($query, $norm, [0, 0, 0]));
        $this->assertNull(VectorService::cosineWithQueryNorm($query, $norm, [1, 0]));

        $guru = User::factory()->create(['role' => 'guru']);
        $matches = [];
        for ($m = 1; $m <= 3; $m++) {
            $module = Module::create([
                'guru_id' => $guru->id,
                'judul' => "Modul {$m}",
                'mapel' => 'TKJ',
                'kb_nomor' => "KB {$m}",
                'file_path' => "modules/m{$m}.pdf",
                'status_indexing' => 'completed',
                'indexing_version' => (string) Str::uuid(),
            ]);
            $c0 = ModuleChunk::create([
                'module_id' => $module->id,
                'chunk_text' => "Pembuka modul {$m}.",
                'embedding_vector' => [0.9, 0.1],
                'embedding_model' => GeminiService::EMBEDDING_MODEL,
                'embedding_dimensions' => 2,
                'chunk_index' => 0,
            ]);
            $c1 = ModuleChunk::create([
                'module_id' => $module->id,
                'chunk_text' => "Inti modul {$m}.",
                'embedding_vector' => [1.0, 0.0],
                'embedding_model' => GeminiService::EMBEDDING_MODEL,
                'embedding_dimensions' => 2,
                'chunk_index' => 1,
            ]);
            $c1->setRelation('module', $module);
            $matches[] = ['chunk' => $c1, 'similarity' => 0.99];
        }

        $chunkQueries = 0;
        DB::listen(function ($query) use (&$chunkQueries) {
            if (str_contains(strtolower($query->sql), 'from "module_chunks"')) {
                $chunkQueries++;
            }
        });

        $sources = app(RetrievalService::class)->sources($matches, 'TKJ');

        $this->assertCount(3, $sources);
        $this->assertSame(1, $chunkQueries, 'RetrievalService::sources() must fetch all neighbor chunks in a single batched query.');
        foreach ($sources as $idx => $source) {
            $num = $idx + 1;
            $this->assertStringContainsString("Pembuka modul {$num}.", $source['text']);
            $this->assertStringContainsString("Inti modul {$num}.", $source['text']);
        }
    }

    public function test_weekly_export_enforces_max_export_rows_limit(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        for ($i = 0; $i < 3; $i++) {
            ChatHistory::create([
                'siswa_id' => $siswa->id,
                'pertanyaan' => "Pertanyaan {$i}",
                'jawaban' => "Jawaban {$i}",
                'kind' => 'answer',
                'mapel' => 'TKJ',
            ]);
        }

        config(['reports.max_export_rows' => 2]);

        $this->actingAs($guru)
            ->from(route('guru.chat-activities.index'))
            ->get(route('guru.chat-activities.export-weekly'))
            ->assertSessionHasErrors('export');
    }

    public function test_login_ip_throttle_blocks_password_spraying_across_different_emails(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->post('/login', [
                'email' => "spray{$i}@smk.sch.id",
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        // 31st login attempt from the same IP (even with a brand-new email) must return 429 Too Many Requests
        $this->post('/login', [
            'email' => 'spray31@smk.sch.id',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_password_update_endpoint_is_rate_limited(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->put(route('password.update'), [
                'current_password' => 'wrong-current',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])->assertStatus(302);
        }

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'wrong-current',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(429);
    }
}
