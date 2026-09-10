# E-Modul Interaktif Terintegrasi AI Chatbot (RAG)

Panduan indexing, worker, dan hasil pengujian terbaru: [Perbaikan dan evaluasi RAG](docs/RAG.md).

### Konsentrasi Keahlian Teknik Komputer dan Jaringan (TKJ) — SMK Negeri 1 Kinali

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-5432-316192?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Google Gemini](https://img.shields.io/badge/Google_Gemini-2.5_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://ai.google.dev/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg?style=for-the-badge)](LICENSE)

---

## 📖 Tentang Sistem

Sistem Pembelajaran Digital **E-Modul Terintegrasi AI Chatbot berbasis Web** ini dikembangkan sebagai karya inovasi media pembelajaran vokasi untuk mendukung pemahaman peserta didik pada mata pelajaran **Teknik Komputer dan Jaringan (TKJ)** di **SMK Negeri 1 Kinali**.

Sistem ini menerapkan arsitektur **Retrieval-Augmented Generation (RAG)** cerdas yang didukung oleh **Google Gemini API**. AI bertindak sebagai asisten pemandu modul yang hanya menjawab pertanyaan berdasarkan isi dokumen materi resmi yang diunggah oleh guru (*Strict Grounding*), sehingga mencegah informasi keliru atau halusinasi AI di luar kurikulum sekolah.

---

## 👨‍💻 Profil Pengembang (Developer)

* **Nama Pengembang** : Fikri Arrahman
* **GitHub** : [@FIKRI-24](https://github.com/FIKRI-24)
* **Kontak Email** : [irkif1011@gmail.com](mailto:irkif1011@gmail.com)
* **Repositori** : [https://github.com/FIKRI-24/RAG_CHATBOT](https://github.com/FIKRI-24/RAG_CHATBOT)
* **Peran** : Full-Stack Developer & AI Engineer (Pengembang & Arsitek Sistem E-Modul RAG)
* **Keterangan Proyek** : Proyek pengembangan media pembelajaran digital berbasis Web dan AI RAG interaktif untuk riset pendidikan vokasi di SMK Negeri 1 Kinali.

---

## ✨ Fitur-Fitur Utama

### 1. Manajemen Akun & Hak Akses Bertingkat (Role-Based)
* **Role Guru:** Akses penuh untuk mengunggah materi, memantau indeks vektor AI, mengelola akun siswa, mengunggah foto profil pengembang, dan memantau analitik real-time.
* **Role Siswa:** Akses katalog e-modul terstruktur, unduh dokumen asli, menonton video praktikum, mengerjakan kuis, dan berdiskusi interaktif dengan Chatbot AI.

### 2. Kurikulum Terstruktur per Kegiatan Belajar (KB)
* Materi terorganisir rapi menjadi unit **KB 1, KB 2, dan KB 3** yang memuat:
  * **Tujuan Pembelajaran (TP)** resmi.
  * Dokumen modul asli (PDF / DOCX) yang dapat diunduh.
  * **Video Pembelajaran Interaktif** (embed YouTube).
  * **Tautan Kuis Evaluasi Fleksibel** (Quizizz, Kahoot, Google Forms, CBT, dsb.).

### 3. Ekstraksi Dokumen & Indexing Vektor Otomatis (RAG Pipeline)
* Unggah berkas dokumen modul dalam format **PDF** atau **Word (.docx)**.
* Sistem mengekstrak teks secara otomatis (`smalot/pdfparser` & `phpoffice/phpword`).
* Melakukan segmentasi teks (*smart chunking*) dengan overlap untuk menjaga keutuhan konteks materi.
* Menghasilkan vektor representasi semantik (*embedding vector*) menggunakan **Gemini Embedding (`text-embedding-004`)**.

### 4. AI Chatbot dengan Strict RAG Grounding (Anti-Halusinasi)
* Siswa dapat bertanya materi dengan bahasa alami (*natural language*).
* Sistem melakukan pencarian kemiripan kosinus (*Cosine Similarity*) untuk mengambil potongan materi yang paling relevan (*Top-K retrieval*).
* Model LLM (**Gemini 2.5 Flash / 2.0 Flash Lite**) menyusun jawaban **hanya dari konteks modul resmi**. Jika jawaban tidak tercantum di modul, AI secara santun memberitahu bahwa materi belum tersedia di modul.

### 5. Mode Latihan Soal AI Interaktif & Evaluasi Otomatis
* Siswa dapat meminta latihan soal interaktif dari materi modul dengan menekan tombol **"Latihan Soal AI"**.
* AI menyusun soal pemahaman berbasis konteks modul.
* Siswa menjawab di kolom chat, dan AI langsung memeriksa serta memberikan skor/evaluasi (*auto-grading*) atas jawaban siswa.

### 6. Dashboard Guru 100% Real-Time
* **Statistik Utama:** Total modul aktif, total potongan teks (chunk), jumlah siswa terdaftar, dan total tanya jawab AI.
* **Grafik Interaksi Bulanan (Chart.js):** Agregasi riil aktivitas pertanyaan siswa dan modul per bulan.
* **Live Feed Aktivitas:** Menampilkan daftar pertanyaan terbaru yang diajukan siswa secara langsung.
* **Indikator Kinerja Sistem:** Rasio kesiapan modul RAG, tingkat partisipasi siswa, dan model AI aktif.

### 7. Profil Pengguna & Profil Pengembang
* Pengguna (Guru & Siswa) dapat mengunggah foto profil akun masing-masing dengan pratinjau langsung.
* Halaman khusus **Profil Pengembang / Peneliti** pada web e-modul dengan pasfoto formal dan panel pembaruan khusus akun Guru.

---

## 🛠️ Arsitektur Teknologi (Tech Stack)

| Komponen | Teknologi yang Digunakan |
| :--- | :--- |
| **Framework Backend** | Laravel 11 (PHP 8.2+) |
| **Basis Data** | PostgreSQL (Database Relasional & Vektor Chunk) |
| **AI LLM & Embedding** | Google Gemini API (`gemini-2.5-flash` & `text-embedding-004`) |
| **Parser Dokumen** | `smalot/pdfparser` (PDF) & `phpoffice/phpword` (DOCX) |
| **Frontend UI** | Blade Templating, Tailwind CSS, Alpine.js |
| **Visualisasi Data** | Chart.js |
| **Ikonografi & Tipografi** | Font Awesome 6, Inter & Figtree Font |
| **Build Tool** | Vite 7 |

---

## 🚀 Panduan Instalasi & Menjalankan Sistem

### 1. Prasyarat Sistem
* PHP >= 8.2 (ekstensi: `pdo`, `pdo_pgsql`, `mbstring`, `zip`, `gd`, `fileinfo`, `curl`)
* Composer >= 2.x
* PostgreSQL >= 14
* Node.js 20.19+ atau 22.12+ & NPM

### 2. Kloning Repositori
```bash
git clone https://github.com/FIKRI-24/RAG_CHATBOT.git
cd RAG_CHATBOT
```

### 3. Instalasi Dependensi
```bash
composer install
npm install
```

Untuk Windows dengan Node lama, pasang runtime khusus proyek (Node sistem tidak diubah):
```powershell
powershell -File scripts/setup-node.ps1
```
`npm run build`, `npm run dev`, dan `npm run test:rag-ui` otomatis memakai runtime tersebut.

### 4. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database PostgreSQL dan Google Gemini API Key Anda:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rag_chatbot
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password

# Google Gemini API Key
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-2.5-flash
```

### 5. Generate Application Key & Storage Link
```bash
php artisan key:generate
php artisan storage:link
```

### 6. Migrasi Database & Seeding
```bash
php artisan migrate --seed
```

### 7. Kompilasi Aset Frontend
```bash
npm run build
```

### 8. Jalankan Server Aplikasi
```bash
php artisan serve
```
Akses aplikasi melalui peramban: `http://127.0.0.1:8000`

---

## 🔒 Keamanan & Privasi

* File konfigurasi rahasia (`.env`) berisi kredensial database dan kunci API dilindungi oleh `.gitignore` dan tidak boleh diunggah ke repositori publik.
* Dokumen modul disimpan secara aman di storage lokal terisolasi.
* Input siswa disaring dan disanitasi sebelum dikirimkan ke model bahasa (*prompt injection protection*).

---

## 📄 Lisensi

Sistem ini dikembangkan oleh **Fikri Arrahman** ([@FIKRI-24](https://github.com/FIKRI-24)) di bawah lisensi [MIT License](LICENSE).
Hak Cipta © 2026 **Fikri Arrahman**. All rights reserved.


## Perbaikan audit fungsional

Rincian perubahan, verifikasi, dan konfigurasi laporan ada di [docs/AUDIT-FIXES.md](docs/AUDIT-FIXES.md).
`composer run dev` menjalankan server, antrean RAG, dan Vite. Pail tidak dijalankan otomatis karena memerlukan ekstensi PCNTL yang tidak tersedia di Windows.
