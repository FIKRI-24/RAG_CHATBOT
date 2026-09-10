<?php

namespace Database\Seeders;

use App\Jobs\ProcessModuleJob;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleKb3Seeder extends Seeder
{
    /**
     * Run the database seeds for Modul Kegiatan Belajar 3 (KB 3).
     */
    public function run(): void
    {
        $guru = User::where('role', 'guru')->first();
        if (! $guru) {
            $this->command?->warn('Modul contoh dilewati: buat akun guru terlebih dahulu.');

            return;
        }

        $tpContent = "Setelah mempelajari KB 3 ini peserta didik dapat:\n1) Mengidentifikasi Frekuensi Radio\n2) Mendiagnosa permasalahan serta melakukan perbaikan Jaringan Nirkabel\n3) Melakukan perawatan jaringan Nirkabel.";

        $module = Module::firstOrCreate(
            [
                'guru_id' => $guru->id,
                'kb_nomor' => 'KB 3',
                'mapel' => 'Teknik Komputer dan Jaringan',
            ],
            [
                'guru_id' => $guru->id,
                'judul' => 'Frekuensi Radio, Permasalahan dan Perbaikan Jaringan Nirkabel, serta Perawatan Jaringan Nirkabel',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kb_nomor' => 'KB 3',
                'tp' => $tpContent,
                'file_path' => 'modules/kb3_jaringan_nirkabel.pdf',
                'video_url' => 'https://drive.google.com/file/d/1J0qNf2095lM5EmtxpvtfyTNaDuESY3r/view?usp=sharing',
                'kuis_url' => 'https://gemini.google.com/share/d/1n1wK_nC_zKWzTYKQD3Vmzr87yeETZl4e?usp=sharing',
                'status_indexing' => 'pending',
                'indexing_version' => (string) Str::uuid(),
                'indexing_error' => null,
            ]
        );

        if ($module->wasRecentlyCreated) {
            ProcessModuleJob::dispatch($module);
        }
    }
}
