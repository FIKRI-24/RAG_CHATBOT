<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\User;
use App\Services\ChunkingService;
use App\Services\DocumentExtractorService;
use App\Services\GeminiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ModuleKb1Seeder extends Seeder
{
    /**
     * Run the database seeds for Modul Kegiatan Belajar 1 (KB 1).
     */
    public function run(): void
    {
        $guru = User::where('role', 'guru')->first();
        if (!$guru) {
            $guru = User::create([
                'name' => 'Guru TKJ',
                'email' => 'guru@tkj.com',
                'password' => bcrypt('password'),
                'role' => 'guru',
            ]);
        }

        $tpContent = "Setelah mempelajari KB 1 ini peserta didik dapat:\n1) Menjelaskan Pengertian Jaringan Nirkabel\n2) Menganalisis Prinsip Kerja Jaringan Nirkabel\n3) Mengidentifikasi Kelebihan dan kelemahan jaringan Nirkabel.";

        $module = Module::updateOrCreate(
            [
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
                'status_indexing' => 'completed',
            ]
        );

        // Hapus chunk lama jika ada
        $module->chunks()->delete();

        // Ekstraksi teks dari berkas PDF
        $fullPath = storage_path('app/private/' . $module->file_path);
        if (file_exists($fullPath)) {
            $extractor = new DocumentExtractorService();
            $text = $extractor->extract($fullPath);

            $chunker = new ChunkingService();
            $chunks = $chunker->chunk($text);

            $gemini = new GeminiService();

            foreach ($chunks as $chunkText) {
                if (empty(trim($chunkText))) {
                    continue;
                }

                try {
                    $embedding = $gemini->embedText($chunkText);
                } catch (\Throwable $e) {
                    Log::warning("Gagal mendapatkan embedding Gemini untuk chunk KB 1: " . $e->getMessage());
                    $embedding = null;
                }

                ModuleChunk::create([
                    'module_id' => $module->id,
                    'chunk_text' => $chunkText,
                    'embedding_vector' => $embedding,
                ]);
            }
        }
    }
}
