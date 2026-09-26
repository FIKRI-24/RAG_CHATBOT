<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleQuiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleKb4Seeder extends Seeder
{
    /**
     * Run the database seeds for Modul Kegiatan Belajar 4 (KB 4).
     */
    public function run(): void
    {
        $guru = User::where('role', 'guru')->first();
        if (! $guru) {
            $this->command?->warn('Modul KB 4 dilewati: buat akun guru terlebih dahulu.');

            return;
        }

        $tpContent = "Setelah mempelajari KB 4 ini peserta didik dapat:\n"
            ."1) Menjelaskan prinsip dasar keamanan jaringan (CIA Triad: Confidentiality, Integrity, Availability)\n"
            ."2) Mengidentifikasi jenis ancaman dan serangan jaringan (Port Scanning, DoS/DDoS, SYN Flood Attack)\n"
            ."3) Menganalisis alur pemrosesan rantai paket firewall (Chain Input, Forward, Output)\n"
            ."4) Membedakan tindakan aturan penyaringan firewall (Action Accept, Drop, Reject)\n"
            ."5) Mengonfigurasi aturan filtering firewall untuk mengamankan router gateway.";

        $module = Module::firstOrCreate(
            [
                'kb_nomor' => 'KB 4',
                'mapel' => 'Teknik Komputer dan Jaringan',
            ],
            [
                'guru_id' => $guru->id,
                'judul' => 'Keamanan Jaringan dan Konfigurasi Firewall Filtering pada Router Gateway',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kb_nomor' => 'KB 4',
                'tp' => $tpContent,
                'file_path' => 'modules/kb4_keamanan_jaringan.docx',
                'video_url' => 'https://www.youtube.com/watch?v=0h9bQx6eTfM',
                'kuis_url' => null,
                'status_indexing' => 'completed',
                'indexing_version' => (string) Str::uuid(),
                'indexing_error' => null,
            ]
        );

        // Jika modul sudah ada tetapi statusnya belum completed, pastikan file_path dan status tepat
        if (! $module->wasRecentlyCreated) {
            $module->update([
                'file_path' => 'modules/kb4_keamanan_jaringan.docx',
                'status_indexing' => 'completed',
            ]);
        }

        $questions = [
            [
                'text' => 'Dalam konsep dasar keamanan informasi (CIA Triad), pilar yang bertugas menjamin bahwa data atau informasi tidak dimanipulasi, diubah, atau dirusak oleh pihak yang tidak sah selama proses transmisi adalah...',
                'options' => [
                    'A' => 'Confidentiality (Kerahasiaan)',
                    'B' => 'Integrity (Keutuhan)',
                    'C' => 'Availability (Ketersediaan)',
                    'D' => 'Authentication (Autentikasi)',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Jenis serangan Denial of Service (DoS) yang mengeksploitasi kelemahan mekanisme three-way handshake TCP dengan membanjiri target dengan paket SYN tanpa pernah mengirimkan paket ACK balasan adalah...',
                'options' => [
                    'A' => 'SYN Flood Attack',
                    'B' => 'ARP Spoofing Attack',
                    'C' => 'Port Scanning Attack',
                    'D' => 'SQL Injection Attack',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Rantai (chain) pada konfigurasi firewall filtering yang menangani paket data yang ditujukan secara spesifik ke router gateway itu sendiri (misalnya teknisi mengakses port SSH 22 atau Winbox) adalah...',
                'options' => [
                    'A' => 'Chain Forward',
                    'B' => 'Chain Output',
                    'C' => 'Chain Input',
                    'D' => 'Chain Prerouting',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Apa perbedaan mendasar antara tindakan filtering Action DROP dan Action REJECT pada firewall router?',
                'options' => [
                    'A' => 'DROP menolak paket dengan pesan balasan ICMP error, sedangkan REJECT membuang paket secara diam-diam',
                    'B' => 'DROP membuang paket secara diam-diam tanpa pesan respon, sedangkan REJECT menolak paket disertai kiriman pesan ICMP error ke pengirim',
                    'C' => 'DROP mengizinkan paket lewat setelah diperiksa, sedangkan REJECT langsung mematikan port antarmuka',
                    'D' => 'DROP hanya berlaku untuk protokol UDP, sedangkan REJECT khusus untuk protokol TCP',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Nomor port default standar yang digunakan oleh protokol Secure Shell (SSH) untuk administrasi jarak jauh router gateway secara terenkripsi dan aman adalah...',
                'options' => [
                    'A' => 'Port 21',
                    'B' => 'Port 22',
                    'C' => 'Port 23',
                    'D' => 'Port 80',
                ],
                'correct_answer' => 'B',
            ],
        ];

        ModuleQuiz::updateOrCreate(
            ['module_id' => $module->id],
            [
                'title' => 'Kuis Evaluasi Pemahaman KB 4: Keamanan Jaringan & Firewall',
                'questions' => $questions,
                'is_published' => true,
                'version' => 1,
            ]
        );
    }
}
