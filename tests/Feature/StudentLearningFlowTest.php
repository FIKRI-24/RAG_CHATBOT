<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentLearningFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_dashboard_redirect_student_to_modules_catalog_and_teacher_to_guru_dashboard(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])
            ->assertRedirect(route('siswa.modules.index', absolute: false));
        $this->get('/dashboard')->assertRedirect(route('siswa.modules.index'));
        $this->post('/logout');

        $this->post('/login', ['email' => $guru->email, 'password' => 'password'])
            ->assertRedirect(route('guru.dashboard', absolute: false));
        $this->get('/dashboard')->assertRedirect(route('guru.dashboard'));
    }

    public function test_student_learning_flow_order_quiz_focus_mode_and_chatbot_assistant(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_name' => 'XI TJKT 2']);

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Dasar VLAN dan Trunking',
            'mapel' => 'Konsentrasi Keahlian TKJ',
            'kb_nomor' => 'KB 1',
            'tp' => 'Siswa mampu menjelaskan fungsi VLAN.',
            'file_path' => 'modules/kb1.pdf',
            'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ]);

        ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => 'VLAN memisahkan broadcast domain pada switch Layer 2.',
            'embedding_vector' => [0.2, 0.3, 0.5],
            'embedding_model' => GeminiService::EMBEDDING_MODEL,
            'embedding_dimensions' => 3,
            'chunk_index' => 0,
        ]);

        $quiz = $module->quiz()->create([
            'title' => 'Kuis KB 1 VLAN',
            'is_published' => true,
            'version' => 1,
            'questions' => [
                ['text' => 'Fungsi VLAN?', 'options' => ['A' => 'Segmentasi L2', 'B' => 'Backup', 'C' => 'Cetak', 'D' => 'Audio'], 'correct_answer' => 'A'],
            ],
        ]);

        // 1. Katalog E-Modul menampilkan alur 1..4 -> Chatbot
        $this->actingAs($siswa)->get(route('siswa.modules.index'))
            ->assertOk()
            ->assertSeeInOrder([
                '1. Pahami TP',
                '2. Pelajari Materi',
                '3. Simak Video',
                '4. Kerjakan Kuis',
                'Chatbot AI (Asisten Tambahan)',
            ]);

        // 2. Halaman detail modul menampilkan urutan 1..4 lalu Asisten Tambahan Chatbot
        $this->actingAs($siswa)->get(route('siswa.modules.show', $module))
            ->assertOk()
            ->assertSeeInOrder([
                '1. Pahami Tujuan Pembelajaran (TP)',
                '2. Pelajari Materi Pembelajaran & Dokumen',
                '3. Simak Video Pembelajaran Pendukung',
                '4. Kerjakan Kuis Evaluasi Pemahaman',
                'Asisten Belajar Tambahan: Tanya Jawab AI (Chatbot RAG)',
            ]);

        // 3. Saat di lembar kuis, link Chatbot di sidebar dinonaktifkan (Mode Fokus Kuis)
        $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module))
            ->assertOk()
            ->assertSee('Mode Fokus Kuis')
            ->assertDontSee('Tanya Jawab AI (Chatbot)');

        // 4. Selesai submit kuis -> di halaman hasil link Chatbot aktif lagi & katalog menampilkan nilai
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id,
            'quiz_version' => $quiz->version,
            'answers' => ['A'],
        ])->assertRedirect();

        $attempt = ModuleQuizAttempt::firstOrFail();

        $this->actingAs($siswa)->get(route('siswa.quiz-attempts.show', $attempt))
            ->assertOk()
            ->assertSee('Tanya Jawab AI (Chatbot)');

        $this->actingAs($siswa)->get(route('siswa.modules.index'))
            ->assertOk()
            ->assertSee('Nilai Kuis: 100/100');

        // 5. Chatbot tetap bisa diakses sebagai asisten lintas KB
        $this->actingAs($siswa)->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Dasar VLAN dan Trunking');
    }
}
