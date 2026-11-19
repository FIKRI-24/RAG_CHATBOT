<?php

namespace Tests\Feature;

use App\Jobs\ProcessModuleJob;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PriorityOneFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeCompletedModule(User $guru, array $overrides = []): Module
    {
        $module = Module::create(array_merge([
            'guru_id' => $guru->id,
            'judul' => 'Jaringan Nirkabel Lanjut',
            'mapel' => 'Konsentrasi Keahlian TKJ',
            'kb_nomor' => 'KB 1',
            'tp' => 'Memahami konsep jaringan nirkabel.',
            'file_path' => 'modules/kb1.pdf',
            'status_indexing' => 'completed',
            'reindexing_status' => null,
            'indexing_version' => (string) Str::uuid(),
        ], $overrides));

        ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => 'Materi inti jaringan nirkabel dan autentikasi.',
            'embedding_vector' => [0.2, 0.4, 0.6],
            'embedding_model' => GeminiService::EMBEDDING_MODEL,
            'embedding_dimensions' => 3,
            'chunk_index' => 0,
        ]);

        return $module;
    }

    public function test_reindexing_completed_module_preserves_live_student_access_and_chunks_even_on_failure(): void
    {
        Storage::fake('local');
        Queue::fake();

        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_name' => 'XI TKJ 1']);

        $module = $this->makeCompletedModule($guru);
        Storage::disk('local')->put($module->file_path, 'original-pdf');
        $module->quiz()->create([
            'title' => 'Kuis KB 1',
            'is_published' => true,
            'version' => 1,
            'questions' => [[
                'text' => 'Standar Wi-Fi?',
                'options' => ['A' => '802.11', 'B' => '802.3', 'C' => '802.15', 'D' => '802.1X'],
                'correct_answer' => 'A',
            ]],
        ]);

        // Guru replaces file while module already has completed index
        $this->actingAs($guru)
            ->put(route('guru.modules.update', $module), [
                'judul' => $module->judul,
                'mapel' => $module->mapel,
                'kb_nomor' => $module->kb_nomor,
                'file' => UploadedFile::fake()->create('kb1-revisi.pdf', 50, 'application/pdf'),
            ])
            ->assertRedirect(route('guru.modules.index'));

        $module->refresh();
        $this->assertSame('completed', $module->status_indexing);
        $this->assertSame('pending', $module->reindexing_status);
        $this->assertTrue(Module::available()->whereKey($module->id)->exists());

        // Student can still open module and quiz during background reindex
        $this->actingAs($siswa)->get(route('siswa.modules.show', $module))->assertOk();
        $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module))->assertOk();

        // Simulate failed re-index job
        $job = new ProcessModuleJob($module, 'modules/kb1.pdf');
        $job->failed(new \RuntimeException('API quota exceeded'));

        $module->refresh();
        $this->assertSame('completed', $module->status_indexing);
        $this->assertSame('failed', $module->reindexing_status);
        $this->assertSame(1, $module->chunks()->count());
        $this->assertTrue(Module::available()->whereKey($module->id)->exists());

        // Guru detail page clearly reports live old index + failed reindex
        $this->actingAs($guru)->get(route('guru.modules.show', $module))
            ->assertOk()
            ->assertSee('Selesai (Siap Tanya AI)')
            ->assertSee('Re-index Gagal (Indeks Lama Aktif)');

        // Successful retry via ProcessModuleJob clears reindexing_status
        Storage::disk('local')->put($module->file_path, '');
        $this->actingAs($guru)->post(route('guru.modules.reindex', $module))->assertRedirect();
        $module->refresh();
        $this->assertSame('pending', $module->reindexing_status);

        $docxPath = 'modules/kb1-ok.docx';
        Storage::disk('local')->put($docxPath, file_get_contents(resource_path('default-modules/kb4_keamanan_jaringan.docx')));

        $module->update([
            'file_path' => $docxPath,
            'indexing_version' => (string) Str::uuid(),
            'reindexing_status' => 'pending',
        ]);

        config(['gemini.api_key' => 'test-key']);

        Http::fake([
            '*:batchEmbedContents' => function ($request) {
                $count = count($request['requests'] ?? []);

                return Http::response([
                    'embeddings' => array_fill(0, $count, ['values' => [0.1, 0.2, 0.3]]),
                ], 200);
            },
            '*' => Http::response(['embedding' => ['values' => [0.1, 0.2, 0.3]]], 200),
        ]);

        (new ProcessModuleJob($module->fresh(), 'modules/kb1.pdf'))->handle(
            app(\App\Services\DocumentExtractorService::class),
            app(\App\Services\ChunkingService::class),
            app(GeminiService::class)
        );

        $module->refresh();
        $this->assertSame('completed', $module->status_indexing);
        $this->assertNull($module->reindexing_status);
        $this->assertGreaterThan(0, $module->chunks()->count());
        Storage::disk('local')->assertMissing('modules/kb1.pdf');
    }

    public function test_stuck_processing_module_can_be_reindexed_from_detail_page_and_shows_index_banner(): void
    {
        Queue::fake();

        $guru = User::factory()->create(['role' => 'guru']);
        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Modul Stuck',
            'mapel' => 'TKJ',
            'kb_nomor' => 'KB 1',
            'file_path' => 'modules/stuck.pdf',
            'status_indexing' => 'processing',
            'indexing_version' => (string) Str::uuid(),
        ]);

        $this->actingAs($guru)->get(route('guru.modules.index'))
            ->assertOk()
            ->assertSee('Pantau Antrean AI');

        $this->actingAs($guru)->get(route('guru.modules.show', $module))
            ->assertOk()
            ->assertSee('Coba Index Ulang');

        $oldVersion = $module->indexing_version;
        $this->actingAs($guru)->post(route('guru.modules.reindex', $module))->assertRedirect();

        $module->refresh();
        $this->assertSame('pending', $module->status_indexing);
        $this->assertNotSame($oldVersion, $module->indexing_version);
        Queue::assertPushed(ProcessModuleJob::class);
    }

    public function test_quiz_recap_retains_expired_reindexing_draft_isolates_teachers_and_filters_metrics(): void
    {
        $guruA = User::factory()->create(['role' => 'guru']);
        $guruB = User::factory()->create(['role' => 'guru']);
        $siswa1 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Kelas Satu', 'class_name' => 'XI TKJ 1']);
        $siswa2 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Kelas Dua', 'class_name' => 'XI TKJ 2']);

        $expiredMod = $this->makeCompletedModule($guruA, [
            'judul' => 'Modul Arsip Kedaluwarsa',
            'kb_nomor' => 'KB 1',
            'berlaku_sampai' => now()->subDays(2)->toDateString(),
        ]);
        $expiredMod->quiz()->create([
            'title' => 'Kuis Arsip',
            'is_published' => true,
            'version' => 1,
            'questions' => [['text' => 'Q1', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'A']],
        ]);

        $draftMod = $this->makeCompletedModule($guruA, [
            'judul' => 'Modul Draf Setelah Attempt',
            'kb_nomor' => 'KB 2',
        ]);
        $draftQuiz = $draftMod->quiz()->create([
            'title' => 'Kuis Draf',
            'is_published' => true,
            'version' => 1,
            'questions' => [['text' => 'Q2', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'B']],
        ]);

        $otherGuruMod = $this->makeCompletedModule($guruB, [
            'judul' => 'Modul Milik Guru Lain',
            'kb_nomor' => 'KB 3',
        ]);
        $otherGuruMod->quiz()->create([
            'title' => 'Kuis Guru B',
            'is_published' => true,
            'version' => 1,
            'questions' => [['text' => 'Q3', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'C']],
        ]);

        // Siswa1 mengerjakan saat draftMod masih aktif/published, siswa2 mengerjakan otherGuruMod
        ModuleQuizAttempt::create([
            'module_id' => $expiredMod->id,
            'user_id' => $siswa1->id,
            'quiz_title' => 'Kuis Arsip',
            'module_title' => $expiredMod->judul,
            'quiz_version' => 1,
            'question_count' => 1,
            'correct_count' => 1,
            'score' => 100,
            'answers' => [0 => 'A'],
            'questions_snapshot' => [['text' => 'Q1', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'A', 'selected_answer' => 'A', 'is_correct' => true]],
        ]);
        $attemptA = ModuleQuizAttempt::create([
            'module_id' => $draftMod->id,
            'user_id' => $siswa1->id,
            'quiz_title' => 'Kuis Draf',
            'module_title' => $draftMod->judul,
            'quiz_version' => 1,
            'question_count' => 1,
            'correct_count' => 1,
            'score' => 100,
            'answers' => [0 => 'B'],
            'questions_snapshot' => [['text' => 'Q2', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'B', 'selected_answer' => 'B', 'is_correct' => true]],
        ]);
        $draftQuiz->update(['is_published' => false]);

        $attemptB = ModuleQuizAttempt::create([
            'module_id' => $otherGuruMod->id,
            'user_id' => $siswa2->id,
            'quiz_title' => 'Kuis Guru B',
            'module_title' => $otherGuruMod->judul,
            'quiz_version' => 1,
            'question_count' => 1,
            'correct_count' => 0,
            'score' => 0,
            'answers' => [0 => 'A'],
            'questions_snapshot' => [['text' => 'Q3', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'C', 'selected_answer' => 'A', 'is_correct' => false]],
        ]);

        // Recap guru A retains both expired and draft modules with attempts, hides guru B's module
        $recapResponse = $this->actingAs($guruA)->get(route('guru.quiz-recap.index'));
        $recapResponse->assertOk()
            ->assertSee('Modul Arsip')
            ->assertSee('Modul Draf')
            ->assertDontSee('Modul Milik Guru')
            ->assertDontSee('$\ge$');

        // Filtered by XI TKJ 1 shows metrics scoped to 1 student
        $filtered = $this->actingAs($guruA)->get(route('guru.quiz-recap.index', ['class_name' => 'XI TKJ 1']));
        $filtered->assertOk()
            ->assertViewHas('metrics', function (array $metrics) {
                return $metrics['total_students'] === 1
                    && $metrics['participating_students'] === 1
                    && $metrics['total_attempts'] === 2
                    && $metrics['average_score'] === 100.0;
            });

        // Cross-teacher IDOR protection on showAttempt
        $this->actingAs($guruA)->get(route('guru.quiz-recap.show-attempt', $attemptA))->assertOk();
        $this->actingAs($guruA)->get(route('guru.quiz-recap.show-attempt', $attemptB))->assertForbidden();
    }

    public function test_quiz_recap_excel_export_uses_module_title_fallback_and_disambiguates_duplicate_kb_headers(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_name' => 'XI TKJ 1']);

        $mod1 = $this->makeCompletedModule($guru, ['judul' => 'KB Satu Pagi', 'kb_nomor' => 'KB 1', 'mapel' => 'Mapel A']);
        $mod2 = $this->makeCompletedModule($guru, ['judul' => 'KB Satu Siang', 'kb_nomor' => 'KB 1', 'mapel' => 'Mapel B']);
        $deletedMod = $this->makeCompletedModule($guru, ['judul' => 'Arsip Dihapus', 'kb_nomor' => 'KB 9']);

        foreach ([$mod1, $mod2, $deletedMod] as $m) {
            $quiz = $m->quiz()->create([
                'title' => 'Kuis '.$m->judul,
                'is_published' => true,
                'version' => 1,
                'questions' => [['text' => 'Q', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'correct_answer' => 'A']],
            ]);
            $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $m), [
                'quiz_id' => $quiz->id,
                'quiz_version' => $quiz->version,
                'answers' => [0 => 'A'],
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        $this->assertSame(3, ModuleQuizAttempt::count());

        // Delete the third module -> module_id becomes NULL on its attempt
        $deletedMod->delete();

        $response = $this->actingAs($guru)->get(route('guru.quiz-recap.export'));
        $response->assertOk();

        $tmpXlsx = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tmpXlsx, $response->streamedContent());

        $spreadsheet = IOFactory::load($tmpXlsx);
        @unlink($tmpXlsx);

        $sheet1 = $spreadsheet->getSheet(0);
        $this->assertSame("KB 1 (#{$mod1->id})", $sheet1->getCell('E13')->getValue());
        $this->assertSame("KB 1 (#{$mod2->id})", $sheet1->getCell('F13')->getValue());

        $sheet2 = $spreadsheet->getSheet(1);
        $logTitles = [];
        for ($row = 4; $row <= 6; $row++) {
            $logTitles[] = $sheet2->getCell("F{$row}")->getValue();
        }
        $this->assertContains('Arsip Dihapus', $logTitles);
    }

    public function test_siswa_dashboard_handles_deleted_module_id_and_renders_em_dash_on_reviewed_quiz(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        $module = $this->makeCompletedModule($guru);

        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertanyaan' => 'Kuis Evaluasi AI',
            'jawaban' => 'Penjelasan evaluasi',
            'kind' => 'quiz',
            'quiz_status' => 'graded',
            'scope_module_id' => $module->id,
            'referensi_chunk_id' => $module->chunks()->first()->id,
            'score' => 90,
            'review_note' => 'Bagus sekali',
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($siswa)->get(route('siswa.dashboard', ['module_id' => 999999]));
        $response->assertOk()
            ->assertSee('Nilai guru: 90/100 — Bagus sekali')
            ->assertDontSee('90/100 ? Bagus sekali');
    }

    public function test_quiz_editor_normalizes_newlines_and_shows_indonesian_validation_errors(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->makeCompletedModule($guru);

        // Windows CRLF newlines that total 2010 bytes with \r\n, but 1340 chars after \n normalization -> accepted
        $multilineText = str_repeat("a\r\n", 670);
        $this->assertGreaterThan(2000, strlen($multilineText));

        $this->actingAs($guru)
            ->put(route('guru.modules.quiz.update', $module), [
                'title' => 'Kuis Normalisasi',
                'is_published' => '1',
                'questions' => [[
                    'text' => $multilineText,
                    'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
                    'correct_answer' => 'A',
                ]],
            ])
            ->assertRedirect();

        $this->assertStringNotContainsString("\r", $module->fresh()->quiz->questions[0]['text']);

        // Missing question text produces Indonesian validation message
        $this->actingAs($guru)
            ->from(route('guru.modules.quiz.edit', $module))
            ->put(route('guru.modules.quiz.update', $module), [
                'title' => 'Kuis Error',
                'questions' => [[
                    'text' => '',
                    'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
                    'correct_answer' => 'A',
                ]],
            ])
            ->assertSessionHasErrors('questions.0.text');
    }

    public function test_weekly_chat_export_respects_module_id_filter(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        $m1 = $this->makeCompletedModule($guru, ['judul' => 'Modul Pertama', 'kb_nomor' => 'KB 1']);
        $m2 = $this->makeCompletedModule($guru, ['judul' => 'Modul Kedua', 'kb_nomor' => 'KB 2']);

        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertanyaan' => 'Pertanyaan Eksklusif Modul Satu',
            'jawaban' => 'Jawaban modul satu',
            'scope_module_id' => $m1->id,
            'referensi_chunk_id' => $m1->chunks()->first()->id,
        ]);
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertanyaan' => 'Pertanyaan Eksklusif Modul Dua',
            'jawaban' => 'Jawaban modul dua',
            'scope_module_id' => $m2->id,
            'referensi_chunk_id' => $m2->chunks()->first()->id,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.chat-activities.export-weekly', ['module_id' => $m1->id]));
        $response->assertOk();

        $tmpXlsx = tempnam(sys_get_temp_dir(), 'weekly');
        file_put_contents($tmpXlsx, $response->streamedContent());

        $sheet = IOFactory::load($tmpXlsx)->getActiveSheet();
        @unlink($tmpXlsx);

        $texts = [];
        for ($r = 11; $r <= 15; $r++) {
            $texts[] = (string) $sheet->getCell("F{$r}")->getValue();
        }
        $joined = implode("\n", $texts);

        $this->assertStringContainsString('Pertanyaan Eksklusif Modul Satu', $joined);
        $this->assertStringNotContainsString('Pertanyaan Eksklusif Modul Dua', $joined);
    }

    public function test_module_store_validates_berlaku_sampai_in_wib_and_normalizes_additional_videos(): void
    {
        Storage::fake('local');
        Queue::fake();

        // 2026-11-18 20:00 UTC == 2026-11-19 03:00 Asia/Jakarta (WIB)
        Carbon::setTestNow(Carbon::create(2026, 11, 18, 20, 0, 0, 'UTC'));
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->post(route('guru.modules.store'), [
                'judul' => 'Modul Tanggal Kemarin WIB',
                'mapel' => 'TKJ',
                'kb_nomor' => 'KB 1',
                'file' => UploadedFile::fake()->create('materi.pdf', 20, 'application/pdf'),
                'berlaku_sampai' => '2026-11-18',
            ])
            ->assertSessionHasErrors('berlaku_sampai');

        $this->actingAs($guru)
            ->post(route('guru.modules.store'), [
                'judul' => 'Modul Hari Ini WIB',
                'mapel' => 'TKJ',
                'kb_nomor' => 'KB 1',
                'file' => UploadedFile::fake()->create('materi.pdf', 20, 'application/pdf'),
                'berlaku_sampai' => '2026-11-19',
                'additional_videos' => [
                    2 => ['title' => 'Video Sparse', 'url' => 'https://youtu.be/dQw4w9WgXcQ'],
                ],
            ])
            ->assertRedirect(route('guru.modules.index'));

        $stored = Module::where('judul', 'Modul Hari Ini WIB')->firstOrFail();
        $this->assertSame([['title' => 'Video Sparse', 'url' => 'https://youtu.be/dQw4w9WgXcQ']], $stored->additional_videos);
    }

    public function test_self_contained_followup_question_skips_rewrite_api_call_while_anaphoric_followup_calls_rewrite(): void
    {
        config(['gemini.api_key' => 'test-key', 'rag.retry_delay_ms' => 0]);
        Http::preventStrayRequests();

        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        $module = $this->makeCompletedModule($guru, ['mapel' => 'Jaringan']);

        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'mapel' => 'Jaringan',
            'kind' => 'answer',
            'pertanyaan' => 'Apa pengertian jaringan nirkabel?',
            'jawaban' => 'Jaringan nirkabel menggunakan gelombang radio.',
            'created_at' => now()->subMinutes(5),
        ]);

        // 1) Self-contained question with history: should ONLY call embedContent (1) + generateContent (1) = 2 HTTP calls
        Http::fake([
            '*:embedContent' => Http::response(['embedding' => ['values' => [0.2, 0.4, 0.6]]], 200),
            '*:generateContent' => Http::response([
                'candidates' => [[
                    'finishReason' => 'STOP',
                    'content' => ['parts' => [['text' => 'Autentikasi jaringan nirkabel menjaga keamanan akses [1].']]],
                ]],
            ], 200),
        ]);

        $this->actingAs($siswa)
            ->postJson(route('siswa.chat.ask'), [
                'pertanyaan' => 'Bagaimana mekanisme autentikasi pada jaringan nirkabel?',
                'mapel' => 'Jaringan',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Http::assertSentCount(2);

        // 2) Anaphoric follow-up question ("Apa contohnya?"): needs rewrite (1) + embed (1) + answer (1) = 3 HTTP calls total (+3 = 5)
        $this->actingAs($siswa)
            ->postJson(route('siswa.chat.ask'), [
                'pertanyaan' => 'Apa contohnya?',
                'mapel' => 'Jaringan',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Http::assertSentCount(5);
    }

    public function test_transient_api_retries_consume_only_one_daily_quota_unit_and_use_wib_date_boundary(): void
    {
        // 2026-11-18 19:30 UTC == 2026-11-19 02:30 WIB
        Carbon::setTestNow(Carbon::create(2026, 11, 18, 19, 30, 0, 'UTC'));
        config(['gemini.api_key' => 'test-key', 'rag.retry_delay_ms' => 0]);
        Http::preventStrayRequests();

        $siswa = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($siswa);

        Http::fake([
            '*' => Http::sequence()
                ->push([], 429)
                ->push([], 503)
                ->push(['embedding' => ['values' => [1, 0]]]),
        ]);

        $vector = app(GeminiService::class)->embedText('VLAN');
        $this->assertSame([1, 0], $vector);
        Http::assertSentCount(3);

        // Even though 3 HTTP attempts were made due to 429/503 retries, only 1 quota unit is consumed under WIB date 2026-11-19
        $this->assertSame(1, (int) \Illuminate\Support\Facades\DB::table('ai_usage')->where('bucket', 'global')->where('day', '2026-11-19')->value('requests'));
        $this->assertSame(1, (int) \Illuminate\Support\Facades\DB::table('ai_usage')->where('bucket', 'user:'.$siswa->id)->where('day', '2026-11-19')->value('requests'));
        $this->assertSame(1, (int) app(\App\Services\SystemStatusService::class)->snapshot()['api_attempts_today']);
    }

    public function test_private_lan_ip_host_headers_are_trusted_for_classroom_access(): void
    {
        foreach (['192.168.1.25:8000', '10.10.0.5', '172.16.5.10:8000', '127.0.0.1:8000'] as $lanHost) {
            $this->withHeader('Host', $lanHost)
                ->get('/login')
                ->assertOk();
        }
    }
}
