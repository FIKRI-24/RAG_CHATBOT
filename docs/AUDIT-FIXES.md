# Perbaikan audit fungsional

Perbaikan ini melanjutkan kode lokal yang belum di-commit. Tidak menjalankan seeder, menghapus data pengguna, atau mengubah timestamp lama di database.

## Perubahan perilaku

- Katalog, detail, unduhan siswa, retrieval, dan badge kedaluwarsa memakai aturan kalender yang sama. Modul harus selesai diindeks dan berlaku sampai akhir tanggal yang dipilih dalam WIB. Guru pemilik tetap bisa mengunduh dokumennya untuk pemeriksaan.
- URL video/kuis harus HTTP(S), maksimal 255 karakter sesuai kolom PostgreSQL. URL terlalu panjang ditolak oleh validasi sebelum file disimpan.
- Upload diperiksa hasilnya. Penggantian file/foto menyimpan pengganti sebelum mengganti record, membersihkan pengganti ketika database gagal, dan menghapus file lama setelah record berhasil disimpan. Penghapusan modul menggunakan transaksi; kegagalan pembersihan file dicatat. Pemeriksaan kepemilikan mengembalikan 403.
- Sumber tunggal riwayat lama disalin sebelum modul dihapus. Ekspor/dashboard menggunakan snapshot sumber, sehingga nama materi tidak hilang sesudah reindex.
- Nama dan avatar pada script chat diserialisasi sebagai JavaScript yang aman. Markdown AI tetap disanitasi DOMPurify, dan sumber ditampilkan sebagai teks.
- Data teks di workbook selalu menjadi string, termasuk teks yang diawali `=`. Jawaban kuis dihitung terpisah dari pertanyaan biasa.
- Penyimpanan timestamp tetap UTC; tampilan dan batas bulan laporan menggunakan Asia/Jakarta. Agregasi dashboard kompatibel dengan PostgreSQL dan SQLite.
- Riwayat chat dipaginasi 30 percakapan per halaman. Pagination katalog dan pengelolaan mempertahankan filter.
- Kuis memeriksa ulang dan mengunci sumber setelah panggilan AI sebelum menyimpan. Sumber yang ditarik/diganti menghasilkan respons 409, bukan kegagalan foreign key.
- Embedding indeks baru ditulis bertahap ke berkas sementara dan dipublikasikan dalam transaksi. Berkas sementara ditutup pada sukses maupun gagal. Duplikat retrieval mempertahankan skor terbaik.
- Rollback migrasi masa berlaku menghapus kolom yang ditambahkan.
- Seeder akun contoh hanya berlaku pada lingkungan local/testing, dapat diulang, dan tidak mengganti password akun yang sudah ada. Seeder modul tidak menimpa modul yang sudah ada atau memanggil AI sinkron.
- Video tambahan bawaan hanya mengikuti dokumen contoh yang sesuai, bukan semua modul dengan nomor KB yang sama.
- Form edit siswa menggunakan URL route, sehingga mendukung instalasi di subfolder.
- `composer run dev` menjalankan server, listener RAG, dan Vite. Pail dikeluarkan dari startup otomatis karena PCNTL tidak tersedia pada PHP Windows.

## Runtime frontend Windows

```powershell
powershell -File scripts/setup-node.ps1
npm run build
npm run test:rag-ui
```

Setup memasang Node 22.23.2 Windows x64 ke `.tools/node/node.exe` dan memeriksa SHA-256 dari distribusi resmi Node.js. Tidak mengubah PATH atau Node sistem. Folder `.tools` tidak masuk Git. Skrip npm memilih runtime lokal jika tersedia; lingkungan lain harus memakai Node 20.19+ atau 22.12+.

## Data administratif laporan

`config/reports.php` menyediakan `teacher_ids` dan `student_classes`, masing-masing berupa pemetaan email akun ke NIP dan kelas. Data yang belum diisi ditampilkan sebagai `-`; aplikasi tidak mengarang NIP atau menganggap semua siswa kelas XII.

Pembacaan data ekspor memakai batch dan tidak memuat embedding. Workbook masih memakai PhpSpreadsheet dan membutuhkan memori sebanding jumlah sel. `REPORT_MAX_EXPORT_ROWS` membatasi jumlah baris siswa + percakapan (default 20.000); laporan yang melampaui batas ditolak dengan pesan yang jelas, tidak dipotong diam-diam. Batas dapat disesuaikan menurut kapasitas server.

## Verifikasi

```sh
php vendor/bin/phpunit --do-not-cache-result
npm run test:rag-ui
npm run build
```

- 80 tes PHP, 358 assertion lulus menggunakan SQLite `:memory:` dan API tiruan.
- 3 tes JavaScript lulus, termasuk nama dengan backtick/interpolasi, sanitasi, dan aksi kuis.
- Build produksi frontend berhasil menggunakan runtime lokal Node 22.23.2.
- 42 template Blade berhasil dikompilasi. Dashboard guru berhasil dirender dengan PostgreSQL lokal melalui pemeriksaan read-only.
- Tes regresi meliputi kedaluwarsa pada pergantian tanggal WIB, penolakan URL panjang, akses unduhan, kegagalan upload/database, snapshot sumber, isi workbook, pagination, seeder berulang, serta perubahan sumber saat API kuis berjalan.

Browser bawaan gagal terhubung pada sesi verifikasi; interaksi browser penuh belum diverifikasi. Tidak ada pengukuran beban serentak atau evaluasi Gemini nyata baru pada pekerjaan ini. Keakuratan LLM, ketersediaan API, batas waktu proxy, dan kapasitas server tetap perlu dipantau. Pencarian vektor masih linear di PHP; dokumen yang melewati timeout indexing perlu dipecah menjadi modul lebih kecil.

Setelah mengganti kode worker, jalankan `php artisan queue:restart`. Jika aplikasi dibuka melalui Apache/XAMPP, jalankan listener dengan `php artisan queue:listen rag --queue=rag --tries=3 --timeout=300`.
