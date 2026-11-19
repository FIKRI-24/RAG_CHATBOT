<?php

namespace Tests\Feature;

use App\Jobs\ProcessModuleJob;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PriorityTwoFixesTest extends TestCase
{
    use RefreshDatabase;

    private function createQuizModule(User $guru, array $overrides = [], ?array $questions = null): Module
    {
        $module = Module::create(array_merge([
            'guru_id' => $guru->id,
            'judul' => 'Materi Jaringan TKJ',
            'mapel' => 'Elemen 1: Perencanaan Jaringan',
            'topik' => 'Topologi',
            'kb_nomor' => 'KB 1',
            'tp' => 'Memahami konsep jaringan.',
            'file_path' => 'modules/kb1.pdf',
            'indexed_file_path' => 'modules/kb1.pdf',
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ], $overrides));

        ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => str_repeat('Materi teknis jaringan komputer TKJ dan konfigurasi perangkat secara rinci. ', 3),
            'embedding_vector' => [0.3, 0.4, 0.5],
            'embedding_model' => GeminiService::EMBEDDING_MODEL,
            'embedding_dimensions' => 3,
            'chunk_index' => 1,
        ]);

        $module->quiz()->create([
            'title' => 'Kuis '.$module->kb_nomor,
            'is_published' => true,
            'version' => 1,
            'questions' => $questions ?? [
                ['text' => 'Soal 1?', 'options' => ['A' => 'Benar', 'B' => 'Salah', 'C' => 'C', 'D' => 'D'], 'correct_answer' => 'A'],
                ['text' => 'Soal 2?', 'options' => ['A' => 'Salah', 'B' => 'Benar', 'C' => 'C', 'D' => 'D'], 'correct_answer' => 'B'],
            ],
        ]);

        return $module;
    }

    public function test_guru_can_approve_revoke_and_student_can_retake_remedial_saving_history_with_latest_score(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $otherGuru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_name' => 'XI TKJ 1']);

        $module = $this->createQuizModule($guru);
        $quiz = $module->quiz;

        // Percobaan awal salah semua -> skor 0
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id,
            'quiz_version' => $quiz->version,
            'answers' => ['B', 'A'],
        ])->assertRedirect();

        $attempt = ModuleQuizAttempt::firstOrFail();
        $this->assertSame(0, (int) $attempt->score);
        $this->assertSame(1, (int) $attempt->attempt_number);

        // Tanpa approval guru, siswa terkunci dari remedial
        $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module))
            ->assertRedirect(route('siswa.quiz-attempts.show', $attempt));

        // Guru lain tidak boleh membuka atau membatalkan remedial (403)
        $this->actingAs($otherGuru)->post(route('guru.quiz-recap.approve-remedial', $attempt))->assertForbidden();
        $this->actingAs($otherGuru)->delete(route('guru.quiz-recap.revoke-remedial', $attempt))->assertForbidden();

        // Guru pemilik approve, lalu tes revoke, lalu approve ulang
        $this->actingAs($guru)->post(route('guru.quiz-recap.approve-remedial', $attempt), [
            'remedial_note' => 'Silakan ulang KB 1',
        ])->assertSessionHas('success');
        $this->assertNotNull($attempt->fresh()->remedial_approved_at);

        $this->actingAs($guru)->delete(route('guru.quiz-recap.revoke-remedial', $attempt))->assertSessionHas('success');
        $this->assertNull($attempt->fresh()->remedial_approved_at);

        $this->actingAs($guru)->post(route('guru.quiz-recap.approve-remedial', $attempt), [
            'remedial_note' => 'Remedial resmi batch 1',
        ])->assertSessionHas('success');

        // Siswa melihat tombol remedial pada detail modul & dapat membuka halaman kuis
        $this->actingAs($siswa)->get(route('siswa.modules.show', $module))
            ->assertOk()
            ->assertSee('Remedial Disetujui')
            ->assertSee('Kerjakan Kuis Remedial');

        $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module))
            ->assertOk()
            ->assertSee('Mode Ujian Ulang Remedial (Percobaan #2)');

        // Siswa submit remedial benar semua -> skor terbaru (100) menggantikan skor lama (0), riwayat tersimpan
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id,
            'quiz_version' => $quiz->version,
            'answers' => ['A', 'B'],
        ])->assertRedirect(route('siswa.quiz-attempts.show', $attempt));

        $attempt->refresh();
        $this->assertSame(100, (int) $attempt->score);
        $this->assertSame(2, (int) $attempt->attempt_number);
        $this->assertNull($attempt->remedial_approved_at);
        $this->assertCount(1, $attempt->historyEntries());
        $this->assertSame(0, $attempt->historyEntries()[0]['score']);
        $this->assertSame('retake', $attempt->historyEntries()[0]['type']);

        // Siswa tidak dapat mengulang ke-3 kali tanpa izin baru
        $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module))
            ->assertRedirect(route('siswa.quiz-attempts.show', $attempt));

        // Halaman hasil siswa & detail guru menampilkan riwayat nilai sebelumnya
        $this->actingAs($siswa)->get(route('siswa.quiz-attempts.show', $attempt))
            ->assertOk()
            ->assertSee('Riwayat Nilai Sebelumnya')
            ->assertSeeInOrder(['Percobaan #1:', '0/100']);

        $this->assertDatabaseHas('audit_events', [
            'action' => 'module.quiz.remedial_submitted',
        ]);
    }

    public function test_guru_can_directly_update_remedial_score_preserving_previous_history(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $otherGuru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_name' => 'XI TKJ 1']);

        $module = $this->createQuizModule($guru);
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $module->quiz->id,
            'quiz_version' => 1,
            'answers' => ['A', 'A'],
        ])->assertRedirect();

        $attempt = ModuleQuizAttempt::firstOrFail();
        $this->assertSame(50, (int) $attempt->score);

        // Cross-teacher forbidden & validation check
        $this->actingAs($otherGuru)->patch(route('guru.quiz-recap.update-remedial-score', $attempt), ['score' => 80])->assertForbidden();
        $this->actingAs($guru)->patch(route('guru.quiz-recap.update-remedial-score', $attempt), ['score' => 120])->assertSessionHasErrors('score');

        // Update nilai remedial langsung oleh guru
        $this->actingAs($guru)->patch(route('guru.quiz-recap.update-remedial-score', $attempt), [
            'score' => 82,
            'remedial_note' => 'Remedial praktik konfigurasi Mikrotik',
        ])->assertSessionHas('success');

        $attempt->refresh();
        $this->assertSame(82, (int) $attempt->score);
        $this->assertNotNull($attempt->adjusted_at);
        $this->assertSame($guru->id, (int) $attempt->adjusted_score_by);
        $this->assertCount(1, $attempt->historyEntries());
        $this->assertSame('manual_adjustment', $attempt->historyEntries()[0]['type']);
        $this->assertSame(50, $attempt->historyEntries()[0]['score']);

        $this->actingAs($guru)->get(route('guru.quiz-recap.show-attempt', $attempt))
            ->assertOk()
            ->assertSee('Nilai Disesuaikan Guru')
            ->assertSee('Sebelum Penyesuaian Guru')
            ->assertSee('Remedial praktik konfigurasi Mikrotik');

        $this->assertDatabaseHas('audit_events', [
            'action' => 'module.quiz.remedial_score_updated',
        ]);
    }

    public function test_quiz_recap_filters_by_mapel_and_unifies_excel_average_and_empty_kpi(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswaA = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa A', 'class_name' => 'XI TKJ 1']);
        $siswaB = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa B', 'class_name' => 'XI TKJ 1']);

        // Cek kondisi 0 modul kuis -> KPI ke-4 tetap terender di Excel (E10:F10)
        $emptyExport = $this->actingAs($guru)->get(route('guru.quiz-recap.export'));
        $emptyExport->assertOk();
        $tmpEmpty = tempnam(sys_get_temp_dir(), 'empty_xlsx');
        file_put_contents($tmpEmpty, $emptyExport->streamedContent());
        $emptySheet = IOFactory::load($tmpEmpty)->getSheet(0);
        @unlink($tmpEmpty);
        $this->assertSame('KETUNTASAN (>= 75)', $emptySheet->getCell('E10')->getValue());

        $mod1 = $this->createQuizModule($guru, ['judul' => 'Elemen Satu Modul', 'mapel' => 'Elemen 1', 'topik' => 'LAN', 'kb_nomor' => 'KB 1']);
        $mod2 = $this->createQuizModule($guru, ['judul' => 'Elemen Dua Modul', 'mapel' => 'Elemen 2', 'topik' => 'WAN', 'kb_nomor' => 'KB 2']);

        // Siswa A: 100 (mod1) dan 0 (mod2); Siswa B: 80 (mod1 via 4/5 or 1/2=50) -> gunakan 50 pada mod1
        // Rata-rata global semua attempt: (100 + 0 + 50) / 3 = 50.0
        $this->actingAs($siswaA)->post(route('siswa.modules.quiz.submit', $mod1), ['quiz_id' => $mod1->quiz->id, 'quiz_version' => 1, 'answers' => ['A', 'B']]);
        $this->actingAs($siswaA)->post(route('siswa.modules.quiz.submit', $mod2), ['quiz_id' => $mod2->quiz->id, 'quiz_version' => 1, 'answers' => ['B', 'A']]);
        $this->actingAs($siswaB)->post(route('siswa.modules.quiz.submit', $mod1), ['quiz_id' => $mod1->quiz->id, 'quiz_version' => 1, 'answers' => ['A', 'A']]);

        // Web recap keseluruhan = 50.0
        $this->actingAs($guru)->get(route('guru.quiz-recap.index'))
            ->assertOk()
            ->assertViewHas('metrics', fn (array $m) => (float) $m['average_score'] === 50.0);

        // Filter web recap by mapel = 'Elemen 1' -> hanya 1 modul, rata-rata = (100+50)/2 = 75.0
        $this->actingAs($guru)->get(route('guru.quiz-recap.index', ['mapel' => 'Elemen 1']))
            ->assertOk()
            ->assertSee('Elemen Satu')
            ->assertDontSee('Elemen Dua')
            ->assertViewHas('metrics', fn (array $m) => $m['total_quizzes'] === 1 && (float) $m['average_score'] === 75.0);

        // Excel Sheet 1 KPI & Summary Row wajib seragam = 50.0
        $exportAll = $this->actingAs($guru)->get(route('guru.quiz-recap.export'));
        $tmpAll = tempnam(sys_get_temp_dir(), 'all_xlsx');
        file_put_contents($tmpAll, $exportAll->streamedContent());
        $sheet1 = IOFactory::load($tmpAll)->getSheet(0);
        @unlink($tmpAll);

        $this->assertSame('50 / 100', $sheet1->getCell('E11')->getValue());
        // Row 14 = Siswa A, Row 15 = Siswa B, Row 16 = RATA-RATA KELAS; G16 = Grand Avg
        $this->assertSame('50.0', $sheet1->getCell('G16')->getValue());
        // Verifikasi metadata kolom D ter-merge rapi (tidak ada gap kolom saat totalColumns=8 -> mid=5 (E), B5:D5 merged)
        $mergedRanges = $sheet1->getMergeCells();
        $this->assertArrayHasKey('B5:D5', $mergedRanges);
    }

    public function test_natural_kb_sorting_shorts_live_embed_and_editor_policy_text(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $kb10 = $this->createQuizModule($guru, [
            'judul' => 'Modul Kesepuluh',
            'kb_nomor' => 'KB 10',
            'video_url' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ',
        ]);
        $kb2 = $this->createQuizModule($guru, [
            'judul' => 'Modul Kedua',
            'kb_nomor' => 'KB 2',
            'video_url' => 'https://www.youtube.com/live/abcdefghijk',
        ]);

        // Katalog siswa mengurutkan KB 2 sebelum KB 10
        $this->actingAs($siswa)->get(route('siswa.modules.index'))
            ->assertOk()
            ->assertSeeInOrder(['KB 2', 'KB 10']);

        // Dashboard chat siswa juga mengurutkan KB 2 sebelum KB 10
        $this->actingAs($siswa)->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['KB 2', 'KB 10']);

        // YouTube Shorts dan Live didukung iframe embed
        $this->actingAs($siswa)->get(route('siswa.modules.show', $kb10))
            ->assertOk()
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ', false);
        $this->actingAs($siswa)->get(route('siswa.modules.show', $kb2))
            ->assertOk()
            ->assertSee('https://www.youtube.com/embed/abcdefghijk', false);

        // Editor kuis menampilkan kebijakan yang akurat, tombol Naik/Turun, serta auto-save draf
        $this->actingAs($guru)->get(route('guru.modules.quiz.edit', $kb2))
            ->assertOk()
            ->assertSee('Secara default siswa mengerjakan 1 kali')
            ->assertSee('Pulihkan Draf')
            ->assertSee('Naik')
            ->assertDontSee('Siswa dapat mengulang latihan; setiap hasil disimpan.');
    }

    public function test_multi_fail_reupload_cleans_original_indexed_file_and_warns_duplicate_module(): void
    {
        Storage::fake('local');
        Queue::fake();

        $guru = User::factory()->create(['role' => 'guru']);
        Storage::disk('local')->put('modules/v1.pdf', 'old-live-file');

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Modul Nirkabel',
            'mapel' => 'TKJ',
            'topik' => 'Dasar Wi-Fi',
            'kb_nomor' => 'KB 1',
            'file_path' => 'modules/v1.pdf',
            'indexed_file_path' => 'modules/v1.pdf',
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ]);
        ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => 'Chunk v1',
            'embedding_vector' => [0.1, 0.2, 0.3],
            'embedding_model' => GeminiService::EMBEDDING_MODEL,
            'embedding_dimensions' => 3,
            'chunk_index' => 0,
        ]);

        // Upload duplikat mapel+topik+kb_nomor memberi warning advisory tanpa memblokir
        $this->actingAs($guru)->post(route('guru.modules.store'), [
            'judul' => 'Modul Duplikat',
            'mapel' => 'TKJ',
            'topik' => 'Dasar Wi-Fi',
            'kb_nomor' => 'KB 1',
            'file' => UploadedFile::fake()->create('dup.pdf', 30, 'application/pdf'),
        ])->assertRedirect(route('guru.modules.index'))->assertSessionHas('warning');

        // Update ke v2 -> gagal -> update lagi ke v3 -> v1 tetap harus terhapus setelah v3 selesai
        $this->actingAs($guru)->put(route('guru.modules.update', $module), [
            'judul' => $module->judul,
            'mapel' => $module->mapel,
            'topik' => $module->topik,
            'kb_nomor' => $module->kb_nomor,
            'file' => UploadedFile::fake()->create('v2.pdf', 30, 'application/pdf'),
        ])->assertRedirect(route('guru.modules.index'));

        $module->refresh();
        $v2Path = $module->file_path;
        (new ProcessModuleJob($module, 'modules/v1.pdf'))->failed(new \RuntimeException('v2 failed'));

        $this->assertSame('failed', $module->fresh()->reindexing_status);
        Storage::disk('local')->assertExists('modules/v1.pdf');

        // Upload v3 (DOCX asli): v2 langsung dihapus sebagai intermediate, v1 menunggu v3 selesai
        $docxUpload = UploadedFile::fake()->createWithContent(
            'v3.docx',
            file_get_contents(resource_path('default-modules/kb4_keamanan_jaringan.docx'))
        );
        $this->actingAs($guru)->put(route('guru.modules.update', $module), [
            'judul' => $module->judul,
            'mapel' => $module->mapel,
            'topik' => $module->topik,
            'kb_nomor' => $module->kb_nomor,
            'file' => $docxUpload,
        ])->assertRedirect(route('guru.modules.index'));

        Storage::disk('local')->assertMissing($v2Path);
        Storage::disk('local')->assertExists('modules/v1.pdf');

        $module->refresh();
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*:batchEmbedContents' => function ($request) {
                $count = count($request['requests'] ?? []);

                return Http::response([
                    'embeddings' => array_fill(0, $count, ['values' => [0.2, 0.3, 0.4]]),
                ], 200);
            },
            '*' => Http::response(['embedding' => ['values' => [0.2, 0.3, 0.4]]], 200),
        ]);

        (new ProcessModuleJob($module))->handle(
            app(\App\Services\DocumentExtractorService::class),
            app(\App\Services\ChunkingService::class),
            app(GeminiService::class)
        );

        Storage::disk('local')->assertMissing('modules/v1.pdf');
        $this->assertSame($module->file_path, $module->fresh()->indexed_file_path);
    }

    public function test_chat_cancel_quizzes_is_scoped_and_catalog_inquiry_handles_multiple_kb_matches(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $m1 = $this->createQuizModule($guru, ['judul' => 'KB Satu Elemen A', 'mapel' => 'Elemen A', 'kb_nomor' => 'KB 1']);
        $m2 = $this->createQuizModule($guru, ['judul' => 'KB Satu Elemen B', 'mapel' => 'Elemen B', 'kb_nomor' => 'KB 1']);

        $quizTab1 = ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertanyaan' => '[LATIHAN_SOAL]',
            'jawaban' => 'Soal Elemen A',
            'kind' => 'quiz',
            'quiz_status' => 'pending',
            'mapel' => 'Elemen A',
            'scope_module_id' => $m1->id,
            'referensi_chunk_id' => $m1->chunks()->first()->id,
        ]);

        // Siswa bertanya di Elemen B -> tidak membatalkan kuis aktif di Elemen A
        $this->actingAs($siswa)->postJson(route('siswa.chat.ask'), [
            'pertanyaan' => 'halo',
            'mapel' => 'Elemen B',
            'module_id' => $m2->id,
        ])->assertOk();
        $this->assertSame('pending', $quizTab1->fresh()->quiz_status);

        // Cancel spesifik quiz_id
        $this->actingAs($siswa)->postJson(route('siswa.chat.ask'), [
            'action' => 'cancel_quiz',
            'quiz_id' => $quizTab1->id,
        ])->assertOk();
        $this->assertSame('cancelled', $quizTab1->fresh()->quiz_status);

        // Catalog inquiry untuk "kb 1" lintas mapel 'Semua' menampilkan kedua Elemen
        $catalogRes = $this->actingAs($siswa)->postJson(route('siswa.chat.ask'), [
            'pertanyaan' => 'apakah ada materi kb 1',
            'mapel' => 'Semua',
        ])->assertOk();

        $answer = $catalogRes->json('data.jawaban');
        $this->assertStringContainsString('KB Satu Elemen A', $answer);
        $this->assertStringContainsString('KB Satu Elemen B', $answer);
    }

    public function test_retrieval_sources_deduplicates_50_character_boundary_overlap_and_passes_chat_hash_verification(): void
    {
        config(['gemini.api_key' => 'test-key', 'rag.retry_delay_ms' => 0]);
        Http::preventStrayRequests();

        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Keamanan Jaringan Nirkabel',
            'mapel' => 'Konsentrasi Keahlian TKJ',
            'kb_nomor' => 'KB 3',
            'file_path' => 'modules/kb3.pdf',
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ]);

        $boundaryPhrase = 'Protokol WPA2-CCMP menggunakan algoritma AES 128-bit.';
        $rawText = str_repeat('Konsep dasar keamanan jaringan nirkabel pada perangkat access point modern. ', 5)
            .$boundaryPhrase.' '
            .str_repeat('Selanjutnya administrator wajib menonaktifkan fitur WPS yang rentan terhadap serangan brute force PIN. ', 5);

        $chunks = app(\App\Services\ChunkingService::class)->chunk($rawText, 500, 50);
        $this->assertGreaterThanOrEqual(2, count($chunks));
        // Both chunk 0 and chunk 1 contain the overlapping boundary text
        $this->assertStringContainsString('AES 128-bit.', $chunks[0]);
        $this->assertStringContainsString('AES 128-bit.', $chunks[1]);

        foreach ($chunks as $idx => $chunkText) {
            ModuleChunk::create([
                'module_id' => $module->id,
                'chunk_text' => $chunkText,
                'embedding_vector' => $idx === 0 ? [1.0, 0.0] : [0.8, 0.6],
                'embedding_model' => GeminiService::EMBEDDING_MODEL,
                'embedding_dimensions' => 2,
                'chunk_index' => $idx,
            ]);
        }

        $retrieval = app(\App\Services\RetrievalService::class);
        $matches = $retrieval->search([1.0, 0.0], 'Konsentrasi Keahlian TKJ', 'Jelaskan algoritma AES 128-bit pada WPA2-CCMP');
        $sources = $retrieval->sources($matches, 'Konsentrasi Keahlian TKJ');

        $this->assertNotEmpty($sources);
        // The overlapping phrase "AES 128-bit." must appear ONLY ONCE in the merged source text, not duplicated!
        $this->assertSame(1, substr_count($sources[0]['text'], 'AES 128-bit.'));

        // Verify ChatController::ask transaction content_hash verification succeeds with merged neighbor chunks
        Http::fake([
            '*:embedContent' => Http::response(['embedding' => ['values' => [1.0, 0.0]]], 200),
            '*:generateContent' => Http::response([
                'candidates' => [[
                    'finishReason' => 'STOP',
                    'content' => ['parts' => [['text' => 'WPA2-CCMP memakai algoritma AES 128-bit [1].']]],
                ]],
            ], 200),
        ]);

        $this->actingAs($siswa)
            ->postJson(route('siswa.chat.ask'), [
                'pertanyaan' => 'Jelaskan algoritma AES 128-bit pada WPA2-CCMP',
                'mapel' => 'Konsentrasi Keahlian TKJ',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_retrieval_lexical_keyword_boost_rescues_technical_terms_and_preserves_original_question_keywords(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Autentikasi Jaringan',
            'mapel' => 'Jaringan',
            'kb_nomor' => 'KB 4',
            'file_path' => 'modules/kb4.pdf',
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ]);

        // Vector [0.60, 0.80] has cosine similarity 0.60 against query vector [1.0, 0.0] (borderline below 0.65 threshold)
        $targetChunk = ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => 'Autentikasi 802.1X menggunakan server RADIUS untuk memvalidasi kredensial pengguna secara terpusat.',
            'embedding_vector' => [0.60, 0.80],
            'embedding_model' => GeminiService::EMBEDDING_MODEL,
            'embedding_dimensions' => 2,
            'chunk_index' => 0,
        ]);

        $retrieval = app(\App\Services\RetrievalService::class);

        // Without query keywords, cosine 0.60 < 0.65 returns empty
        $this->assertEmpty($retrieval->search([1.0, 0.0], 'Jaringan'));

        // With student's technical keywords ("802.1X", "RADIUS"), lexical boost lifts similarity >= 0.65
        $matches = $retrieval->search([1.0, 0.0], 'Jaringan', 'Bagaimana autentikasi 802.1X dengan server RADIUS?');
        $this->assertCount(1, $matches);
        $this->assertSame($targetChunk->id, $matches[0]['chunk']->id);
        $this->assertGreaterThanOrEqual(0.65, $matches[0]['similarity']);

        // Even if rewriteQuestion drops "RADIUS", ChatController combines rewritten query + original question so "RADIUS" is still matched
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'mapel' => 'Jaringan',
            'kind' => 'answer',
            'pertanyaan' => 'Apa itu autentikasi terpusat?',
            'jawaban' => 'Autentikasi terpusat memakai server khusus.',
            'created_at' => now()->subMinutes(3),
        ]);

        $mock = \Mockery::mock(GeminiService::class);
        $mock->shouldReceive('startBudget')->once();
        $mock->shouldReceive('rewriteQuestion')->once()->andReturn('Bagaimana cara kerja autentikasi terpusat?');
        $mock->shouldReceive('embedText')->once()->andReturn([1.0, 0.0]);
        $mock->shouldReceive('generateAnswer')->once()->andReturn('Autentikasi 802.1X memakai server RADIUS [1].');
        $this->app->instance(GeminiService::class, $mock);

        $this->actingAs($siswa)
            ->postJson(route('siswa.chat.ask'), [
                'pertanyaan' => 'Kalau pakai RADIUS 802.1X itu bagaimana?',
                'mapel' => 'Jaringan',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sources.0.chunk_id', $targetChunk->id);
    }

    public function test_student_chat_dashboard_renders_isolated_copy_selector_and_safe_non_json_error_handling(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->get(route('siswa.dashboard'));
        $response->assertOk()
            ->assertSee("container?.querySelector('.rendered-content')", false)
            ->assertSee("document.execCommand('copy')", false)
            ->assertSee('Sesi halaman telah berakhir. Silakan muat ulang (refresh) halaman ini.', false)
            ->assertSee('Waktu tunggu habis (Timeout).', false);
    }
}
