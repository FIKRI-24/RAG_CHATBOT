<?php

namespace Database\Seeders;

use App\Jobs\ProcessModuleJob;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleKb2Seeder extends Seeder
{
    /**
     * Run the database seeds for Modul Kegiatan Belajar 2 (KB 2).
     */
    public function run(): void
    {
        $guru = User::where('role', 'guru')->first();
        if (! $guru) {
            $this->command?->warn('Modul contoh dilewati: buat akun guru terlebih dahulu.');

            return;
        }

        $tpContent = "Setelah mempelajari KB 2 ini peserta didik dapat:\n1) Mengidentifikasi jenis-jenis Jaringan Nirkabel\n2) Mengidentifikasi Perangkat Jaringan Nirkabel\n3) Menganalisis Standar Wi-Fi (IEEE 802.11) jaringan Nirkabel.";

        $module = Module::firstOrCreate(
            [
                'guru_id' => $guru->id,
                'kb_nomor' => 'KB 2',
                'mapel' => 'Teknik Komputer dan Jaringan',
            ],
            [
                'guru_id' => $guru->id,
                'judul' => 'Jenis-Jenis Jaringan Nirkabel, Perangkat Jaringan Nirkabel, dan Standar Wi-Fi (IEEE 802.11)',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kb_nomor' => 'KB 2',
                'tp' => $tpContent,
                'file_path' => 'modules/kb2_jaringan_nirkabel.pdf',
                'video_url' => 'https://www.youtube.com/watch?v=2YSkp6K9fc4',
                'kuis_url' => 'https://gemini.google.com/share/d/1w4zIjpWucuxsPwligelL_DQP5xaaw8kT?usp=sharing',
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
