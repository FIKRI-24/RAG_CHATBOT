<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatbotGreetingAndKb4Test extends TestCase
{
    use RefreshDatabase;

    private function createModules(User $guru): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $module = Module::create([
                'guru_id' => $guru->id,
                'judul' => 'Judul Materi KB ' . $i,
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kb_nomor' => 'KB ' . $i,
                'tp' => 'Tujuan Pembelajaran KB ' . $i,
                'file_path' => 'modules/kb' . $i . '.pdf',
                'status_indexing' => 'completed',
            ]);

            ModuleChunk::create([
                'module_id' => $module->id,
                'chunk_text' => 'Teks materi modul KB ' . $i . ' tentang jaringan komputer dan firewall.',
                'chunk_index' => 0,
                'embedding_model' => GeminiService::EMBEDDING_MODEL,
                'embedding_dimensions' => 3072,
                'embedding_vector' => array_fill(0, 3072, 0.05 * $i),
            ]);
        }
    }

    public function test_chatbot_responds_politely_to_greeting(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $this->createModules($guru);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $greetings = ['halo', 'hai', 'selamat pagi', 'assalamualaikum', 'kamu siapa'];

        foreach ($greetings as $greet) {
            $response = $this->actingAs($siswa)->postJson(route('siswa.chat.ask'), [
                'pertanyaan' => $greet,
                'mapel' => 'Semua',
            ]);

            $response->assertOk();
            $response->assertJsonPath('success', true);
            $this->assertStringContainsString('Asisten AI Pembelajaran TKJ', $response->json('data.jawaban'));
            $this->assertStringContainsString('KB 1', $response->json('data.jawaban'));
            $this->assertStringContainsString('KB 4', $response->json('data.jawaban'));
            $this->assertEmpty($response->json('data.sources'));
        }
    }

    public function test_chatbot_answers_catalog_inquiry_for_specific_kb(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $this->createModules($guru);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->postJson(route('siswa.chat.ask'), [
            'pertanyaan' => 'apakah ada materi kb 4?',
            'mapel' => 'Semua',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $jawaban = $response->json('data.jawaban');

        $this->assertStringContainsString('KB 4', $jawaban);
        $this->assertStringContainsString('aktif dan tersedia', $jawaban);
        $this->assertStringContainsString('Tujuan Pembelajaran KB 4', $jawaban);
    }

    public function test_chatbot_answers_general_catalog_inquiry(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $this->createModules($guru);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->postJson(route('siswa.chat.ask'), [
            'pertanyaan' => 'ada materi apa saja di sistem?',
            'mapel' => 'Semua',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $jawaban = $response->json('data.jawaban');

        $this->assertStringContainsString('4 Kegiatan Belajar (KB)', $jawaban);
        $this->assertStringContainsString('KB 1', $jawaban);
        $this->assertStringContainsString('KB 2', $jawaban);
        $this->assertStringContainsString('KB 3', $jawaban);
        $this->assertStringContainsString('KB 4', $jawaban);
    }
}
