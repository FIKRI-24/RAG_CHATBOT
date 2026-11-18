<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModuleCurriculumFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_and_siswa_module_filters_include_five_curriculum_elements_topic_and_kbs(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Pengertian Jaringan Nirkabel',
            'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
            'topik' => 'Instalasi Jaringan Nirkabel',
            'kb_nomor' => 'KB 1',
            'file_path' => 'modules/kb1.pdf',
            'status_indexing' => 'completed',
        ]);

        Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Firewall Filtering Router',
            'mapel' => 'Elemen 3: Keamanan Jaringan',
            'topik' => 'Keamanan Jaringan dan Firewall',
            'kb_nomor' => 'KB 4',
            'file_path' => 'modules/kb4.docx',
            'status_indexing' => 'completed',
        ]);

        $guruIndex = $this->actingAs($guru)->get(route('guru.modules.index'));
        $guruIndex->assertOk();
        foreach (Module::curriculumElements() as $element) {
            $guruIndex->assertSee($element);
        }
        $guruIndex->assertSee('Instalasi Jaringan Nirkabel');
        $guruIndex->assertSee('KB 1');
        $guruIndex->assertSee('KB 2');
        $guruIndex->assertSee('KB 3');
        $guruIndex->assertSee('KB 4');

        $filteredGuru = $this->actingAs($guru)->get(route('guru.modules.index', [
            'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
            'topik' => 'Instalasi Jaringan Nirkabel',
            'kb_nomor' => 'KB 1',
        ]));
        $filteredGuru->assertOk();
        $filteredGuru->assertSee('Pengertian Jaringan Nirkabel');
        $filteredGuru->assertDontSee('Firewall Filtering Router');

        $siswaIndex = $this->actingAs($siswa)->get(route('siswa.modules.index', [
            'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
            'topik' => 'Instalasi Jaringan Nirkabel',
            'kb_nomor' => 'KB 1',
        ]));
        $siswaIndex->assertOk();
        $siswaIndex->assertSee('Pengertian Jaringan Nirkabel');
        $siswaIndex->assertDontSee('Firewall Filtering Router');
    }

    public function test_guru_can_store_and_update_module_with_element_and_topic(): void
    {
        Queue::fake();
        Storage::fake('local');

        $guru = User::factory()->create(['role' => 'guru']);

        $storeResponse = $this->actingAs($guru)->post(route('guru.modules.store'), [
            'judul' => 'Konfigurasi Access Point',
            'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
            'topik' => 'Instalasi Jaringan Nirkabel',
            'kb_nomor' => 'KB 2',
            'file' => UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'),
        ]);

        $storeResponse->assertRedirect(route('guru.modules.index'));
        $module = Module::where('judul', 'Konfigurasi Access Point')->firstOrFail();
        $this->assertSame('Elemen 2: Teknologi Jaringan Kabel dan Nirkabel', $module->mapel);
        $this->assertSame('Instalasi Jaringan Nirkabel', $module->topik);
        $this->assertSame('KB 2', $module->kb_nomor);

        $updateResponse = $this->actingAs($guru)->put(route('guru.modules.update', $module), [
            'judul' => 'Perawatan Jaringan Nirkabel',
            'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
            'topik' => 'Instalasi Jaringan Nirkabel',
            'kb_nomor' => 'KB 3',
        ]);

        $updateResponse->assertRedirect(route('guru.modules.index'));
        $module->refresh();
        $this->assertSame('Perawatan Jaringan Nirkabel', $module->judul);
        $this->assertSame('KB 3', $module->kb_nomor);
        $this->assertSame('Instalasi Jaringan Nirkabel', $module->topik);
    }

    public function test_guru_dashboard_shows_accurate_indexing_counts_failed_badge_and_sidebar_links(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Modul Siap',
            'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
            'topik' => 'Instalasi Jaringan Nirkabel',
            'kb_nomor' => 'KB 1',
            'file_path' => 'modules/kb1.pdf',
            'status_indexing' => 'completed',
        ]);

        Module::create([
            'guru_id' => $guru->id,
            'judul' => 'Modul Gagal Scan',
            'mapel' => 'Elemen 3: Keamanan Jaringan',
            'topik' => 'Keamanan Jaringan dan Firewall',
            'kb_nomor' => 'KB 4',
            'file_path' => 'modules/kb4.pdf',
            'status_indexing' => 'failed',
        ]);

        $response = $this->actingAs($guru)->get(route('guru.dashboard'));
        $response->assertOk();
        $response->assertViewHas('modulCompleted', 1);
        $response->assertSee('1 dari 2 materi terindeks', false);
        $response->assertSee('Gagal');
        $response->assertSee('Aktivitas Tanya Jawab');
        $response->assertDontSee('Status Sistem');

        $this->actingAs($guru)->get(route('guru.system'))->assertOk();
    }
}
