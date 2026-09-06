<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessModuleJob;

class ModuleKbAndPetunjukTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_upload_module_with_kb_fields(): void
    {
        Storage::fake('local');
        Queue::fake();

        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($guru)->post(route('guru.modules.store'), [
            'judul' => 'Dasar VLAN dan Switch',
            'mapel' => 'Administrasi Infrastruktur Jaringan',
            'kb_nomor' => 'KB 1',
            'tp' => 'Memahami konsep VLAN dan mode port.',
            'video_url' => 'https://www.youtube.com/watch?v=example123',
            'kuis_url' => 'https://forms.gle/exampleQuiz',
            'file' => UploadedFile::fake()->create('modul_vlan.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect(route('guru.modules.index'));

        Queue::assertPushed(ProcessModuleJob::class);

        $this->assertDatabaseHas('modules', [
            'judul' => 'Dasar VLAN dan Switch',
            'kb_nomor' => 'KB 1',
            'mapel' => 'Administrasi Infrastruktur Jaringan',
            'video_url' => 'https://www.youtube.com/watch?v=example123',
            'kuis_url' => 'https://forms.gle/exampleQuiz',
        ]);
    }

    public function test_guru_can_edit_module_kb_and_tp(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Konfigurasi Routing OSPF',
            'mapel' => 'Routing Dinamis',
            'kb_nomor' => 'KB 1',
            'tp' => 'Tujuan awal.',
            'file_path' => 'modules/test.pdf',
            'status_indexing' => 'completed',
        ]);

        $response = $this->actingAs($guru)->put(route('guru.modules.update', $module->id), [
            'judul' => 'Konfigurasi Routing OSPF Lanjutan',
            'mapel' => 'Routing Dinamis',
            'kb_nomor' => 'KB 2',
            'tp' => 'Tujuan pembelajaran diperbarui untuk multi-area.',
            'video_url' => 'https://www.youtube.com/watch?v=ospfVideo',
            'kuis_url' => 'https://forms.gle/ospfQuiz',
        ]);

        $response->assertRedirect(route('guru.modules.index'));

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'judul' => 'Konfigurasi Routing OSPF Lanjutan',
            'kb_nomor' => 'KB 2',
            'tp' => 'Tujuan pembelajaran diperbarui untuk multi-area.',
        ]);
    }

    public function test_guru_can_access_petunjuk_page(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($guru)->get(route('guru.petunjuk'));

        $response->assertStatus(200);
        $response->assertSee('Petunjuk Penggunaan Sistem E-Modul', false);
        $response->assertSee('Alur Kerja Guru (4 Tahapan Utama)', false);
        $response->assertSee('Tujuan Pembelajaran (TP)', false);
    }

    public function test_siswa_can_access_petunjuk_page(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->get(route('siswa.petunjuk'));

        $response->assertStatus(200);
        $response->assertSee('Petunjuk Penggunaan E-Modul');
        $response->assertSee('Pahami TP');
    }

    public function test_siswa_can_access_modules_catalog_and_show(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Kabel UTP dan Crimping RJ-45',
            'mapel' => 'Jaringan Dasar',
            'kb_nomor' => 'KB 1',
            'tp' => 'Siswa mampu melakukan crimping kabel straight dan cross.',
            'video_url' => 'https://www.youtube.com/watch?v=crimpingVideo',
            'kuis_url' => 'https://forms.gle/crimpingQuiz',
            'file_path' => 'modules/crimping.pdf',
            'status_indexing' => 'completed',
        ]);

        // Test catalog index
        $indexResponse = $this->actingAs($siswa)->get(route('siswa.modules.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Kabel UTP dan Crimping RJ-45');
        $indexResponse->assertSee('KB 1');

        // Test module detail show
        $showResponse = $this->actingAs($siswa)->get(route('siswa.modules.show', $module->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Tujuan Pembelajaran (TP)');
        $showResponse->assertSee('Siswa mampu melakukan crimping');
        $showResponse->assertSee('Unduh Dokumen Materi');
    }

    public function test_authenticated_user_can_download_module_file(): void
    {
        Storage::fake('local');

        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $fakeFile = UploadedFile::fake()->create('panduan_tkj.pdf', 100, 'application/pdf');
        $path = $fakeFile->store('modules', 'local');

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Panduan TKJ',
            'mapel' => 'Jaringan',
            'kb_nomor' => 'KB 1',
            'file_path' => $path,
            'status_indexing' => 'completed',
        ]);

        // Siswa download
        $response = $this->actingAs($siswa)->get(route('modules.download', $module->id));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_guru_can_delete_module_safely(): void
    {
        Storage::fake('local');

        $guru = User::factory()->create(['role' => 'guru']);

        $fakeFile = UploadedFile::fake()->create('modul_hapus.pdf', 100, 'application/pdf');
        $path = $fakeFile->store('modules', 'local');

        $module = Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Modul Hapus Test',
            'mapel' => 'Jaringan',
            'kb_nomor' => 'KB 1',
            'file_path' => $path,
            'status_indexing' => 'completed',
        ]);

        $response = $this->actingAs($guru)->delete(route('guru.modules.destroy', $module->id));

        $response->assertRedirect(route('guru.modules.index'));
        $response->assertSessionHas('success', 'Modul berhasil dihapus.');

        $this->assertDatabaseMissing('modules', [
            'id' => $module->id,
        ]);

        Storage::disk('local')->assertMissing($path);
    }

    public function test_authenticated_user_can_view_profil_pengembang(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        // Test Guru access
        $guruResponse = $this->actingAs($guru)->get(route('pengembang'));
        $guruResponse->assertStatus(200);
        $guruResponse->assertSee('Rudi Putra');
        $guruResponse->assertSee('25040030002');
        $guruResponse->assertSee('Pendidikan Guru Vokasi');
        $guruResponse->assertSee('Universitas PGRI Sumatera Barat');
        $guruResponse->assertSee('putrarudi238@gmail.com');

        // Test Siswa access
        $siswaResponse = $this->actingAs($siswa)->get(route('pengembang'));
        $siswaResponse->assertStatus(200);
        $siswaResponse->assertSee('Rudi Putra');
        $siswaResponse->assertSee('E-Modul terintegrasi Chatbot berbasis Web');
        $siswaResponse->assertSee('putrarudi238@gmail.com');
    }
}
