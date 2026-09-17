# Operasional lokal dan persiapan deployment

Panduan ini mengikuti keputusan pemilik proyek: aplikasi masih lokal, semua guru boleh mengelola seluruh siswa dan membaca transkrip, dan pendaftaran mandiri ditutup di produksi. Tidak ada deployment, email nyata, atau unggahan backup ke layanan luar yang dilakukan.

## Menjalankan aplikasi

Gunakan PHP 8.2+, PostgreSQL, serta Node 20.19+ atau 22.12+. Skrip npm otomatis memakai runtime `.tools/node` bila tersedia di Windows.

```powershell
composer install
npm ci
php artisan migrate
npm run build
```

Jalankan web melalui Apache/XAMPP dengan document root menunjuk `public`, atau `php artisan serve`. Jalankan worker di terminal terpisah:

```powershell
powershell -File scripts/start-rag-worker.ps1
```

Worker mendengarkan **koneksi `rag`, antrean `rag`**, bukan sekadar antrean pada koneksi default. Di Windows skrip menggunakan `queue:listen` agar batas waktu proses anak dapat diterapkan. Timeout job 300 detik dan `retry_after` 360 detik. Indexing memiliki anggaran panggilan AI 270 detik dan batas 200 chunk per modul. Hentikan dengan Ctrl+C dan jalankan kembali setelah perubahan kode/config. Jika menggunakan `composer run dev`, listener RAG sudah termasuk; jangan menjalankan listener kedua tanpa kebutuhan.

Halaman guru **Status sistem** dan perintah berikut memeriksa database, antrean, modul gagal, konfigurasi AI dan penggunaan API tanpa mengirim materi ke luar:

```powershell
php artisan system:check
php artisan rag:check-index --strict
```

`system:check --strict` keluar dengan kode gagal jika worker belum teramati, kunci AI tidak tersedia, pekerjaan/modul gagal, atau antrean tertua melebihi 600 detik. Heartbeat merupakan aktivitas worker yang teramati dengan masa berlaku 390 detik. Heartbeat bukan bukti pasti bahwa proses masih hidup, dan keberadaan kunci bukan bukti koneksi Gemini sehat. `/up` hanya merupakan pemeriksaan bootstrap Laravel; gunakan pemeriksaan sistem untuk database/antrean.

## Konfigurasi akun dan AI

| Pengaturan | Perilaku |
|---|---|
| `APP_ENV=local` | Pendaftaran mandiri terbuka secara default untuk pengembangan |
| `APP_ENV=production` | Pendaftaran mandiri tertutup secara default |
| `REGISTRATION_ENABLED=false` | Menutup GET dan POST pendaftaran, termasuk di lokal |
| `REQUIRE_VERIFIED_EMAIL=false` | Verifikasi email tidak wajib untuk pemakaian lokal |
| `REQUIRE_VERIFIED_EMAIL=true` | Dashboard, materi dan chat membutuhkan email terverifikasi; profil tetap dapat diperbaiki |
| `APP_URL` | URL dasar tepercaya untuk tautan reset password, termasuk port/prefix aplikasi lokal |
| `APP_TRUSTED_HOSTS` | Daftar hostname tambahan, dipisahkan koma |
| `APP_TRUSTED_PROXIES` | Alamat proxy tepercaya; kosong untuk akses lokal langsung |
| `RAG_REQUEST_BUDGET_SECONDS=55` | Anggaran panggilan AI per permintaan chat, termasuk retry |
| `RAG_DAILY_USER_LIMIT=100` | Batas percobaan API per akun per hari UTC |
| `RAG_DAILY_GLOBAL_LIMIT=2000` | Batas percobaan API seluruh aplikasi, termasuk indexing dan evaluasi |
| `RAG_MAX_CHUNKS_PER_MODULE=200` | Dokumen lebih panjang perlu dipisahkan per KB |

Kuota menghitung **setiap percobaan HTTP**, termasuk retry dan permintaan yang gagal. Angka ini bukan token atau biaya uang. Kenaikan kuota global dan akun memakai transaksi dan kunci baris. Tanpa sumber, jawaban penolakan tidak menggunakan generation. Pembatalan kuis tidak memakai API.

Gunakan **Nonaktifkan** untuk menghentikan akses siswa sambil mempertahankan riwayat. Penghapusan tetap permanen dan memerlukan konfirmasi. Reset/perubahan password serta penonaktifan memutar token pengingat, meningkatkan versi autentikasi, dan mencabut sesi database. Guru yang masih memiliki modul wajib memindahkannya ke guru aktif lain sebelum menghapus akun. Guru aktif terakhir tidak dapat dihapus.

Kelas dan NIS diisi pada manajemen siswa. NIP/nomor guru diisi pada profil guru. Konfigurasi `reports.php` hanya menjadi fallback untuk data administratif lama. Statistik keaktifan berarti pernah memiliki interaksi belajar, termasuk kuis; status akun aktif/nonaktif merupakan pengaturan login yang berbeda. Statistik halaman dihitung saat dimuat, bukan diperbarui lewat push.

Kuis baru menyimpan soal, kunci dan rubrik di server. Kunci tidak dikirim melalui respons siswa. Nilai AI 0–100 merupakan saran. Guru meninjau di **Tinjauan kuis**, memasukkan nilai dan catatan; nilai guru terlihat pada riwayat siswa setelah dimuat kembali dan tercantum pada ekspor transkrip tanpa menghapus saran AI asli. Kuis lama tanpa rubrik harus dibuat kembali sebelum dinilai.

## Backup dan pemulihan

Hentikan worker dan tunggu job yang sedang berjalan selesai sebelum backup. Backup menolak pekerjaan yang masih memiliki reservasi. Mode maintenance menghentikan perubahan dari web agar dump dan berkas konsisten. Jangan memakai `queue:clear` untuk menyingkirkan reservasi; periksa pekerjaan dan pulihkan secara terkendali.

```powershell
php artisan down
try {
    php artisan backup:create --verify
} finally {
    php artisan up
}
```

Arsip berada di `storage/app/backups`, di luar disk publik dan diabaikan Git. Isinya dump seluruh database, berkas modul, avatar, foto pengembang, serta manifest SHA-256 dan jumlah baris. `.env`, API key, dan APP_KEY tidak dimasukkan. Simpan APP_KEY dan kredensial secara terpisah agar pemulihan layanan lengkap dimungkinkan. Arsip berisi hash password dan riwayat siswa; simpan salinannya di media terlindungi. Arsip lokal belum dienkripsi dan checksum mendeteksi kerusakan, bukan membuktikan asal pengirim.

Untuk PostgreSQL, `PG_BIN` menunjuk direktori `pg_dump` dan `pg_restore`. Default Windows adalah `C:/Program Files/PostgreSQL/18/bin`; sesuaikan instalasi Anda. Gunakan versi alat yang kompatibel dengan server. Password proses diteruskan melalui environment, bukan argumen atau log.

`--verify` memeriksa semua checksum, memulihkan dump ke database baru bernama `rag_restore_<acak>`, membandingkan jumlah baris, lalu menghapus hanya database sementara tersebut. Akun PostgreSQL memerlukan izin membuat database untuk latihan ini. Database aplikasi tidak ditimpa. Pada SQLite, salinan dibuka terpisah, `integrity_check` dan jumlah baris diperiksa. Pengujian browser juga memastikan backup yang isi databasenya diubah ditolak.

Pemulihan nyata dilakukan saat web maintenance dan worker berhenti:

1. Verifikasi arsip terlebih dahulu. Sediakan **database baru yang kosong** dan direktori berkas tujuan terpisah.
2. Untuk PostgreSQL, pulihkan `database/database.dump` dengan `pg_restore --no-owner --no-acl --exit-on-error` ke database baru. Untuk SQLite, gunakan salinan `database/database.sqlite` setelah pemeriksaan integritas.
3. Salin `files/local` ke root disk lokal dan `files/public` ke root disk publik. Cocokkan checksum manifest dan keberadaan berkas yang dirujuk modul/avatar/foto pengembang.
4. Arahkan konfigurasi database ke hasil pemulihan, pulihkan APP_KEY melalui penyimpanan terpisah, dan jalankan `system:check` serta `rag:check-index --strict`.
5. Uji login, akses modul, transkrip, dan satu kuis sebelum membuka maintenance dan menjalankan worker. Jangan menunjuk database asal sebagai target latihan restore.

Backup tidak memiliki penghapusan otomatis. Untuk pemakaian sekolah, tetapkan jadwal harian, retensi yang sesuai kebutuhan sekolah dan ruang disk, salinan di media lain, serta latihan pemulihan berkala. Hosting/penyimpanan luar belum dikonfigurasi karena aplikasi masih lokal.

## Pengujian dan evaluasi

```powershell
php artisan test
php tests/Support/run-postgres-suite.php
npm run test:rag-ui
npm run test:e2e
npm run test:load
npm run build
```

Helper PostgreSQL hanya menerima koneksi lokal dan membuat database pengujian baru yang dihapus setelah selesai. Jangan mengarahkan PHPUnit ke database aplikasi. E2E dan load test membuat SQLite baru di `.tools/e2e/run-*`, memakai berkas fixture dan layanan AI tiruan yang hanya dimuat oleh bootstrap pengujian. Port server dipilih terpisah untuk mencegah dua runner memakai server yang sama. E2E menggunakan Chromium; instal dengan `npx playwright install chromium` jika belum tersedia. Hasil kegagalan browser berada di `test-results` dan `playwright-report`; fixture dan laporan load berada pada direktori run masing-masing.

Workflow `.github/workflows/tests.yml` menjalankan SQLite, PostgreSQL, sanitasi UI, browser, load, build dan audit dependensi. Workflow disiapkan di repositori; belum dijalankan di GitHub.

Evaluasi Gemini berikut **mengirim pertanyaan, riwayat contoh, dan potongan modul ke Google**, menggunakan API yang sudah dikonfigurasi, serta tidak menulis riwayat siswa:

```powershell
php artisan rag:evaluate tests/Fixtures/rag-evaluation.json --generate --output=storage/app/rag-evaluation.jsonl
```

Jalankan setelah menyetujui pengiriman materi untuk evaluasi. Tanpa `--generate`, pertanyaan dan riwayat contoh tetap dikirim untuk rewrite/embedding. Fixture berisi 15 kasus dasar KB 1/KB 2, parafrasa, pergantian topik, luar korpus, dan instruksi berbahaya. Sesuaikan kata kunci, `expected_modules` dan `module_id` dengan korpus. Hasil mencatat pass rate retrieval, precision/recall judul modul jika ground truth judul disediakan, error dan p95. Kata kunci/precision retrieval tidak membuktikan bahwa jawaban LLM benar. Guru tetap perlu menilai dukungan setiap kutipan, kelengkapan jawaban, penolakan di luar materi, dan ketahanan terhadap instruksi berbahaya di pertanyaan/dokumen.

## Jika nanti dipindahkan ke produksi

Gunakan `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS yang benar, `REGISTRATION_ENABLED=false`, cookie sesi aman, layanan email sekolah, serta daftar host/proxy sesuai hosting. Jangan membuka verifikasi wajib sebelum email berfungsi. Pakai `LOG_CHANNEL=daily` dan retensi log, bukan file tunggal tanpa rotasi. Lindungi berkas modul pada disk privat dan pastikan persistent volume mencakup database/files sesuai arsitektur hosting.

Pastikan extension PHP `pcntl` tersedia pada worker Linux untuk timeout Laravel. `deploy/supervisor-rag.conf` menyediakan worker Linux dengan restart otomatis, koneksi RAG eksplisit, batas waktu, penghentian satu grup proses, dan rotasi log. Sesuaikan path, executable PHP dan akun proses sebelum memasangnya. `nixpacks.toml` menggunakan start bawaan Nginx/PHP-FPM, mempertahankan paket provider, tidak memakai `--ignore-platform-reqs`, serta tidak menjalankan migrasi otomatis pada setiap start. Jalankan migrasi sebagai tugas release yang diawasi; tempatkan worker dalam layanan terpisah dan jalankan `nixpacks plan` untuk memeriksa hasil sebelum deployment. Konfigurasi hosting tersebut belum diuji dengan deployment nyata.

Rujukan: [Laravel Queue](https://laravel.com/framework/docs/12.x/queues), [provider PHP Nixpacks](https://nixpacks.com/docs/providers/php), [penggabungan konfigurasi Nixpacks](https://nixpacks.com/docs/configuration/file), [Gemini GenerateContent](https://ai.google.dev/api/generate-content).
