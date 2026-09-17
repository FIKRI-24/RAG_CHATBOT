# Kuis objektif per modul / KB

Guru pemilik modul dapat membuat satu kuis pilihan ganda langsung di aplikasi. Setiap soal memiliki empat pilihan A–D dan satu jawaban benar. Kuis berisi 1–50 soal, semua soal berbobot sama, dan nilai dihitung server dengan rumus `round(jumlah benar / jumlah soal × 100)` tanpa API AI.

## Penggunaan guru

1. Buka **Manajemen Modul**, lalu pilih **Kelola Kuis** pada modul/KB yang diinginkan. Tautan juga tersedia pada detail dan halaman edit modul.
2. Isi judul, pertanyaan, empat pilihan dan jawaban benar. Gunakan **Tambah Soal** / **Hapus Soal** untuk mengatur soal.
3. Klik **Simpan Kuis**. Centang **Terbitkan kuis untuk siswa** agar siswa dapat mengerjakannya; tanpa centang, kuis tersimpan sebagai draf.
4. Buka **Lihat Hasil Siswa** untuk melihat siswa, versi kuis, jumlah benar, nilai, dan waktu pengerjaan.
5. Edit, simpan sebagai draf, atau hapus kuis bila diperlukan. Hasil lama tetap tersimpan ketika kuis diedit atau dihapus.

Pada unggahan modul baru, simpan modul terlebih dahulu lalu pilih **Kelola Kuis**. Link kuis eksternal tetap opsional dan dapat digunakan bersama kuis internal.

## Penggunaan siswa

1. Buka detail modul pada **Katalog E-Modul**. Modul harus tersedia dan belum kedaluwarsa.
2. Pilih **Kerjakan Kuis Objektif**. Tombol muncul hanya untuk kuis yang diterbitkan.
3. Jawab seluruh soal, lalu klik **Kirim Jawaban**.
4. Lihat nilai, jawaban sendiri, dan jawaban benar. Latihan boleh diulang; setiap pengiriman yang valid menyimpan hasil baru.

Sepuluh hasil terakhir untuk modul tersebut tersedia pada halaman latihan. Halaman hasil hanya dapat diakses siswa pemiliknya. Kunci jawaban tidak dikirim dalam halaman pengerjaan; kunci ditampilkan setelah pengiriman valid. Fitur ini merupakan latihan dengan pembahasan, tanpa timer atau batas satu percobaan.

Jika guru memperbarui kuis saat halaman siswa masih terbuka, pengiriman dari versi lama ditolak. Siswa perlu memuat ulang dan menjawab versi terbaru. Hasil yang sudah disimpan menyimpan salinan soal dan kunci dari versi yang dikerjakan sehingga nilainya tetap sama setelah edit atau penghapusan kuis.

## Penerapan dan verifikasi lokal

Migrasi tambahan `2026_09_14_000001_create_module_quizzes` menambahkan tabel `module_quizzes` dan `module_quiz_attempts`. Migrasi sudah diterapkan pada PostgreSQL lokal. Data akun, modul, chunk dan percakapan yang ada dipertahankan. Backup database mencakup kedua tabel baru dan verifikasi restore memeriksa jumlah barisnya.

Untuk checkout lain yang belum menerapkan migrasi:

```powershell
php artisan migrate
npm run build
```

Tes fitur memeriksa pengelolaan/publikasi, hak akses, validasi, kunci yang tidak bocor sebelum pengerjaan, nilai yang tidak dapat ditentukan klien, versi lama, dan hasil yang bertahan setelah edit/penghapusan. Tes browser mencakup editor dinamis, alur guru–siswa, navigasi layar kecil, penilaian dan penarikan kuis.

Hasil verifikasi: 109 tes PHP / 514 assertions lulus di SQLite dan PostgreSQL terpisah, 5 tes browser lulus, dan build Vite berhasil. Pengujian tidak membuat akun, kuis, atau hasil latihan pada database utama. Hash data akun, modul, chunk, percakapan, audit dan profil pengembang cocok sebelum/sesudah migrasi; jumlah data utama tetap 5 akun, 3 modul, 53 chunk dan 32 percakapan.
