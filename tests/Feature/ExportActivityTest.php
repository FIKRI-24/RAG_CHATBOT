<?php

namespace Tests\Feature;

use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_download_excel_activity_report(): void
    {
        $guru = User::factory()->create([
            'name' => 'Rudi Putra, S.Pd.',
            'role' => 'guru',
        ]);

        $siswa = User::factory()->create([
            'name' => 'Ahmad Siswa',
            'email' => 'ahmad@smk.id',
            'role' => 'siswa',
        ]);

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Teknologi Jaringan Nirkabel',
            'mapel' => 'Administrasi Infrastruktur Jaringan',
            'kb_nomor' => 'KB 1',
            'tp' => 'Memahami konsep nirkabel',
            'file_path' => 'modules/test.pdf',
        ]);

        $chunk = ModuleChunk::create([
            'module_id' => $module->id,
            'chunk_text' => 'Potongan teks materi nirkabel',
            'embedding_vector' => [0.1, 0.2, 0.3],
        ]);

        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertanyaan' => 'Apa itu nirkabel?',
            'jawaban' => 'Nirkabel adalah transmisi data tanpa kabel.',
            'referensi_chunk_id' => $chunk->id,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.siswa.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename="REKAP_AKTIVITAS_SISWA_SMKN1_KINALI_', $response->headers->get('Content-Disposition'));
    }

    public function test_siswa_cannot_access_export_activity_route(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->get(route('guru.siswa.export'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_export_activity_route(): void
    {
        $response = $this->get(route('guru.siswa.export'));

        $response->assertRedirect(route('login'));
    }
}
