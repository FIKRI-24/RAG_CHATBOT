<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizSingleAttemptAndRecapTest extends TestCase
{
    use RefreshDatabase;

    private function createQuizModule(User $guru, string $kbNomor = 'KB 1'): Module
    {
        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Materi ' . $kbNomor,
            'mapel' => 'Teknik Komputer dan Jaringan',
            'kb_nomor' => $kbNomor,
            'tp' => 'Tujuan Pembelajaran ' . $kbNomor,
            'file_path' => 'modules/test_' . strtolower(str_replace(' ', '_', $kbNomor)) . '.pdf',
            'status_indexing' => 'completed',
        ]);

        $module->quiz()->create([
            'title' => 'Kuis Objektif ' . $kbNomor,
            'is_published' => true,
            'questions' => [
                [
                    'text' => 'Soal 1 ' . $kbNomor,
                    'options' => ['A' => 'Opsi A', 'B' => 'Opsi B', 'C' => 'Opsi C', 'D' => 'Opsi D'],
                    'correct_answer' => 'A',
                ],
                [
                    'text' => 'Soal 2 ' . $kbNomor,
                    'options' => ['A' => 'Opsi A', 'B' => 'Opsi B', 'C' => 'Opsi C', 'D' => 'Opsi D'],
                    'correct_answer' => 'B',
                ],
            ],
        ]);

        return $module;
    }

    public function test_student_can_take_quiz_only_once_and_subsequent_attempts_are_blocked(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->createQuizModule($guru, 'KB 1');
        $quiz = $module->quiz;

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'name' => 'Budi Pratama',
            'class_name' => 'XII TKJ 1',
        ]);

        // 1. Siswa belum mengerjakan: halaman kuis dapat diakses dan menampilkan peringatan 1 kali
        $response = $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module));
        $response->assertOk();
        $response->assertSee('1 (satu) kali');

        // 2. Siswa mengirimkan jawaban kuis pertama kali
        $submitResponse = $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id,
            'quiz_version' => $quiz->version,
            'answers' => ['A', 'B'], // Keduanya benar -> skor 100
        ]);

        $this->assertDatabaseCount('module_quiz_attempts', 1);
        $attempt = ModuleQuizAttempt::firstOrFail();
        $this->assertEquals(100, $attempt->score);
        $submitResponse->assertRedirect(route('siswa.quiz-attempts.show', $attempt));

        // 3. Siswa mencoba membuka kembali halaman pengerjaan kuis
        // Harus dialihkan (redirect) ke halaman hasil kuis dengan pesan peringatan
        $secondVisitResponse = $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module));
        $secondVisitResponse->assertRedirect(route('siswa.quiz-attempts.show', $attempt));
        $secondVisitResponse->assertSessionHas('info', 'Anda telah menyelesaikan kuis ini. Kuis hanya dapat dikerjakan satu kali.');

        // 4. Siswa mencoba mengirim ulang via POST (misal lewat form replay / inspect element)
        // Harus ditolak dan dialihkan kembali tanpa menambah record database
        $secondSubmitResponse = $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id,
            'quiz_version' => $quiz->version,
            'answers' => ['A', 'C'],
        ]);

        $secondSubmitResponse->assertRedirect(route('siswa.quiz-attempts.show', $attempt));
        $secondSubmitResponse->assertSessionHas('warning', 'Anda sudah pernah mengerjakan kuis ini. Kuis hanya dapat dikerjakan satu kali.');
        $this->assertDatabaseCount('module_quiz_attempts', 1);
        $this->assertEquals(100, $attempt->fresh()->score);
    }

    public function test_module_detail_displays_completed_quiz_card_with_score(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->createQuizModule($guru, 'KB 2');
        $quiz = $module->quiz;

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'name' => 'Citra Dewi',
            'class_name' => 'XII TKJ 2',
        ]);

        // Sebelum kuis dikerjakan: tombol 'Kerjakan Kuis Objektif' muncul
        $this->actingAs($siswa)->get(route('siswa.modules.show', $module))
            ->assertOk()
            ->assertSee('Kerjakan Kuis Objektif')
            ->assertSee('1x Kesempatan');

        // Kerjakan kuis
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id,
            'quiz_version' => $quiz->version,
            'answers' => ['A', 'A'], // 1 benar, 1 salah -> skor 50
        ]);

        $attempt = ModuleQuizAttempt::firstOrFail();

        // Setelah kuis dikerjakan: kartu 'Sudah Dikerjakan' muncul dengan skor dan link review
        $this->actingAs($siswa)->get(route('siswa.modules.show', $module))
            ->assertOk()
            ->assertSee('Sudah Dikerjakan')
            ->assertSee('50/100')
            ->assertSee('Lihat Rincian Jawaban')
            ->assertDontSee('Kerjakan Kuis Objektif');
    }

    public function test_teacher_can_view_quiz_recap_and_filter(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Guru Pengampu']);
        $modul1 = $this->createQuizModule($guru, 'KB 1');
        $modul2 = $this->createQuizModule($guru, 'KB 2');

        $siswaA = User::factory()->create([
            'role' => 'siswa',
            'name' => 'Ahmad Dani',
            'student_number' => '1001',
            'class_name' => 'XII TKJ 1',
        ]);

        $siswaB = User::factory()->create([
            'role' => 'siswa',
            'name' => 'Bambang Sudirman',
            'student_number' => '1002',
            'class_name' => 'XII TKJ 2',
        ]);

        // Siswa A kerjakan KB 1 (skor 100) dan KB 2 (skor 50)
        ModuleQuizAttempt::create([
            'module_id' => $modul1->id,
            'module_quiz_id' => $modul1->quiz->id,
            'user_id' => $siswaA->id,
            'module_title' => $modul1->judul,
            'quiz_title' => $modul1->quiz->title,
            'quiz_version' => 1,
            'score' => 100,
            'correct_count' => 2,
            'question_count' => 2,
            'answers' => ['A', 'B'],
            'questions_snapshot' => $modul1->quiz->questions,
        ]);

        ModuleQuizAttempt::create([
            'module_id' => $modul2->id,
            'module_quiz_id' => $modul2->quiz->id,
            'user_id' => $siswaA->id,
            'module_title' => $modul2->judul,
            'quiz_title' => $modul2->quiz->title,
            'quiz_version' => 1,
            'score' => 50,
            'correct_count' => 1,
            'question_count' => 2,
            'answers' => ['A', 'A'],
            'questions_snapshot' => $modul2->quiz->questions,
        ]);

        // Guru mengakses rekap kuis
        $response = $this->actingAs($guru)->get(route('guru.quiz-recap.index'));
        $response->assertOk();
        $response->assertSee('Rekapitulasi Nilai Kuis Objektif Siswa');
        $response->assertSee('Ahmad Dani');
        $response->assertSee('Bambang Sudirman');
        $response->assertSee('KB 1');
        $response->assertSee('KB 2');
        $response->assertSee('100');

        // Filter kelas XII TKJ 1: hanya menampilkan Siswa A
        $filterResponse = $this->actingAs($guru)->get(route('guru.quiz-recap.index', ['class_name' => 'XII TKJ 1']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Ahmad Dani');
        $filterResponse->assertDontSee('Bambang Sudirman');

        // Search siswa Bambang: hanya menampilkan Siswa B
        $searchResponse = $this->actingAs($guru)->get(route('guru.quiz-recap.index', ['search' => 'Bambang']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Bambang Sudirman');
        $searchResponse->assertDontSee('Ahmad Dani');
    }

    public function test_teacher_can_view_single_attempt_detail(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->createQuizModule($guru, 'KB 3');
        $siswa = User::factory()->create(['role' => 'siswa', 'name' => 'Dina Mariana']);

        $attempt = ModuleQuizAttempt::create([
            'module_id' => $module->id,
            'module_quiz_id' => $module->quiz->id,
            'user_id' => $siswa->id,
            'module_title' => $module->judul,
            'quiz_title' => $module->quiz->title,
            'quiz_version' => 1,
            'score' => 50,
            'correct_count' => 1,
            'question_count' => 2,
            'answers' => ['A', 'D'],
            'questions_snapshot' => $module->quiz->questions,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.quiz-recap.show-attempt', $attempt));
        $response->assertOk();
        $response->assertSee('Lembar Jawaban Siswa');
        $response->assertSee('Dina Mariana');
        $response->assertSee('Soal 1 KB 3');
        $response->assertSee('Kunci Jawaban Benar');
    }

    public function test_teacher_can_export_quiz_recap_excel(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Pak Guru TKJ']);
        $module = $this->createQuizModule($guru, 'KB 1');
        $siswa = User::factory()->create(['role' => 'siswa', 'name' => 'Eko Prasetyo']);

        ModuleQuizAttempt::create([
            'module_id' => $module->id,
            'module_quiz_id' => $module->quiz->id,
            'user_id' => $siswa->id,
            'module_title' => $module->judul,
            'quiz_title' => $module->quiz->title,
            'quiz_version' => 1,
            'score' => 100,
            'correct_count' => 2,
            'question_count' => 2,
            'answers' => ['A', 'B'],
            'questions_snapshot' => $module->quiz->questions,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.quiz-recap.export'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename="REKAP_NILAI_KUIS_SMKN1_KINALI_', $response->headers->get('Content-Disposition'));
    }

    public function test_student_and_guest_cannot_access_quiz_recap(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($siswa)->get(route('guru.quiz-recap.index'))->assertStatus(403);
        $this->actingAs($siswa)->get(route('guru.quiz-recap.export'))->assertStatus(403);

        auth()->logout();

        $this->get(route('guru.quiz-recap.index'))->assertRedirect(route('login'));
        $this->get(route('guru.quiz-recap.export'))->assertRedirect(route('login'));
    }
}
