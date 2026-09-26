<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModuleKb4Seeder extends Seeder
{
    /**
     * Run the database seeds for Modul Kegiatan Belajar 4 (KB 4).
     */
    public function run(): void
    {
        $guruId = Module::whereIn('kb_nomor', ['KB 1', 'KB 2', 'KB 3'])->value('guru_id')
            ?? User::where('role', 'guru')->value('id');

        if (! $guruId) {
            $this->command?->warn('Modul KB 4 dilewati: buat akun guru terlebih dahulu.');

            return;
        }

        Module::ensureKb4Module($guruId);
    }
}
