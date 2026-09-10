<?php

namespace Database\Seeders;

use App\Jobs\ProcessModuleJob;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleKb1Seeder extends Seeder
{
    /**
     * Run the database seeds for Modul Kegiatan Belajar 1 (KB 1).
     */
    public function run(): void
    {
        $guru = User::where('role', 'guru')->first();
        if (! $guru) {
            $this->command?->warn('Modul contoh dilewati: buat akun guru terlebih dahulu.');

            return;
        }

        $tpContent = "Setelah mempelajari KB 1 ini peserta didik dapat:\n1) Menjelaskan Pengertian Jaringan Nirkabel\n2) Menganalisis Prinsip Kerja Jaringan Nirkabel\n3) Mengidentifikasi Kelebihan dan kelemahan jaringan Nirkabel.";

        $module = Module::firstOrCreate(
            [
                'guru_id' => $guru->id,
                'kb_nomor' => 'KB 1',
                'mapel' => 'Teknik Komputer dan Jaringan',
            ],
            [
                'guru_id' => $guru->id,
                'judul' => 'Pengertian, Prinsip Kerja, serta Kelebihan dan Kelemahan Jaringan Nirkabel',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kb_nomor' => 'KB 1',
                'tp' => $tpContent,
                'file_path' => 'modules/kb1_jaringan_nirkabel.pdf',
                'video_url' => 'https://www.youtube.com/watch?v=uryCL0DSu9w',
                'kuis_url' => 'https://gemini.google.com/share/d/1wd5jzitWbU2pycqTXLpeFVuE4B0NTMDR?usp=sharing',
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
