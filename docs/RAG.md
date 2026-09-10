# Perbaikan dan evaluasi RAG

Perubahan dibatasi pada indexing dokumen, retrieval/generation, kuis, rendering jawaban, dan pengujian RAG. Halaman autentikasi tidak diubah.

## Menjalankan

```sh
php artisan migrate
npm install
npm run build
composer run dev
```

`composer run dev` sudah menjalankan listener koneksi `rag`. Jika web dijalankan melalui Apache/XAMPP atau `php artisan serve` secara terpisah, jalankan terminal worker:

```sh
php artisan queue:listen rag --queue=rag --tries=3 --timeout=300
```

Koneksi `rag` menggunakan tabel `jobs`, antrian `rag`, dan `retry_after=360`. Indexing tetap asynchronous meskipun `.env` aplikasi berisi `QUEUE_CONNECTION=sync`. Timeout job 300 detik, maksimal tiga percobaan, backoff 15/60 detik. Pada Windows, gunakan listener di atas agar timeout proses anak dapat diterapkan. Worker Linux dapat memakai `queue:work rag --queue=rag --tries=3 --timeout=300` di bawah process supervisor. Restart worker setelah perubahan kode/config.

## Perilaku yang diperbaiki

1. **Integritas indeks.** Pemotongan maksimal 500 karakter Unicode dengan overlap 50, termasuk paragraf pertama yang panjang. DOCX mempertahankan pemisah paragraf dan sel tabel. Dokumen tanpa teks dan embedding kosong/rusak ditolak. Model, dimensi, dan urutan chunk dicatat.
2. **Indexing.** Chunk baru disiapkan sebelum transaksi penggantian indeks. Kegagalan tidak menyisakan chunk baru parsial; indeks lama tetap tersimpan. Hanya modul `completed` dan belum kedaluwarsa yang dapat dicari. Selama reindex, modul sementara tidak masuk retrieval. Penanda versi mencegah job lama/ulang menimpa indeks terbaru. Penjelasan kegagalan aman ditampilkan ke guru. Tombol reindex tersedia pada detail modul selesai/gagal. Seeder modul memakai job yang sama.
3. **Jawaban.** Pencarian memuat vektor per 100 baris dan mempertahankan top-k, menolak dimensi berbeda, serta menghapus duplikat teks dari kandidat terbaik. Setiap anchor harus lolos threshold. Jendela satu chunk sebelum/sesudah anchor menjaga kelanjutan daftar/langkah; sumber mencatat seluruh `chunk_ids`, sedangkan skor tetap milik anchor. Tanpa sumber, jawaban penolakan dibuat langsung tanpa generation. Maksimal tiga giliran terakhir selama 30 menit, pada siswa dan mapel yang sama, dipakai untuk merumuskan pertanyaan mandiri. Riwayat bukan bukti jawaban: generation tetap memakai materi hasil retrieval baru. Snapshot judul, mapel, KB, teks, ID, dan skor disimpan/ditampilkan. Referensi tunggal riwayat lama disalin sebelum chunk dihapus; sumber tambahan yang tidak pernah disimpan pada versi lama tidak dapat dipulihkan.
4. **Kuis dan tampilan.** Aksi `ask`, `quiz`, `quiz_answer`, `cancel_quiz` eksplisit. Kuis memiliki status pending/answered/cancelled dan ID milik siswa. Kuis lintas pengguna/mapel, replay, dan sumber yang dihapus/ditarik ditolak. UI menampilkan mode jawaban kuis dan tombol kembali ke tanya jawab. Permintaan siswa dikunci selama proses agar tidak saling menimpa. HTML dari jawaban/riwayat disanitasi DOMPurify dengan daftar tag terbatas; sumber ditampilkan sebagai teks yang di-escape.
5. **Evaluasi.** Test regresi PHP, sanitasi/aksi UI dengan jsdom, dan perintah evaluasi nyata disediakan. Log jawaban mencatat durasi, jumlah sumber, dan skor tanpa isi percakapan atau API key. API memakai header key, timeout koneksi/request, retry terbatas untuk 429/5xx/koneksi, serta pesan error aman.

## Menjalankan pengujian

```sh
php vendor/bin/phpunit --do-not-cache-result
npm run test:rag-ui
npm run build
```

PHPUnit menggunakan SQLite `:memory:`, fake/mock API, dan storage pengujian. Pemeriksaan DOCX menggunakan berkas DOCX nyata yang dibuat di storage pengujian. UI test menjalankan script chat yang sebenarnya dengan nilai Blade fixture; ini bukan pengujian browser penuh atas server login.

Evaluasi nyata berikut menggunakan API Gemini dan indeks database saat ini, tetapi tidak menulis riwayat siswa:

```sh
php artisan rag:evaluate tests/Fixtures/rag-evaluation.json --generate
```

Tanpa `--generate`, hanya retrieval yang dievaluasi. Fixture contoh mengikuti modul KB 1 jaringan nirkabel yang tersedia saat perbaikan. Sesuaikan mapel dan `expected_terms` untuk korpus lain. `expect_empty` memeriksa penolakan retrieval. `retrieval_pass` memeriksa kelengkapan kata kunci/ketiadaan konteks, **bukan** pembuktian otomatis akurasi atau grounding jawaban LLM; periksa jawaban dan sumber secara manual.

## Hasil lokal 7 September 2026

- Seluruh 62 test PHP (256 assertion), tiga test JavaScript, dan build frontend lulus.
- Migrasi RAG diterapkan ke database lokal.
- Satu modul lokal diindeks ulang melalui queue dan Gemini: 8 chunk lama menjadi 14 chunk, maksimal 495 karakter, dimensi 3072; selesai sekitar 10 detik.
- Evaluasi awal menemukan daftar kelemahan terpotong dan skor pertanyaan resep yang tidak relevan sekitar 0,53. Sesudah jendela tetangga ditambahkan dan threshold awal dinaikkan ke 0,65, lima kasus berikut lulus pemeriksaan retrieval.

| Kasus | Hasil yang diperiksa | Durasi satu pengukuran |
| --- | --- | --- |
| Fungsi jaringan nirkabel | Jawaban menghubungkan perangkat tanpa kabel melalui gelombang elektromagnetik, dengan sumber | 2,978 detik |
| Kelebihan dan kelemahan | Empat kelebihan dan empat kelemahan tercakup, termasuk interferensi | 3,811 detik |
| Lanjutan “Bagaimana cara kerjanya?” | Dirumuskan menjadi cara kerja jaringan nirkabel; empat langkah dijelaskan | 6,353 detik |
| Mapel di luar korpus | Tidak ada sumber; penolakan langsung | 0,742 detik |
| Resep rendang | Tidak ada sumber; penolakan langsung | 0,795 detik |

Durasi berasal dari CLI evaluasi (API + retrieval, serta rewrite/generation jika diperlukan), bukan pengukuran HTTP/browser atau p95. Evaluator tetap melakukan embedding pada mapel kosong; endpoint chat menghindari panggilan itu. Target semua jawaban di bawah lima detik belum terbukti, khususnya pertanyaan lanjutan.

## Batas yang masih perlu dipantau

- Threshold `RAG_SIMILARITY_THRESHOLD` default 0,65 adalah titik awal untuk korpus kecil ini. Evaluasi lagi dengan pertanyaan representatif dari setiap mapel; lima kasus bukan tolok ukur akurasi umum.
- PDF scan tanpa teks ditolak dengan petunjuk OCR; mesin OCR tidak ditambahkan. Metadata halaman belum tersedia; UI tidak mengarang nomor halaman.
- Pencarian masih cosine di PHP, dengan memori retrieval dibatasi batch. Kompleksitas tetap mengikuti jumlah vektor; belum bermigrasi ke pgvector. Embedding calon indeks ditulis bertahap ke berkas sementara sebelum publikasi dalam transaksi. Dokumen sangat besar/lambat masih bisa melampaui timeout job dan perlu dipecah.
- Riwayat lama tanpa metadata embedding diperlakukan sebagai model hard-coded lama yang sama, lalu divalidasi saat dibaca. Jika asal model indeks pernah berbeda, lakukan reindex; metadata yang tidak pernah dicatat tidak bisa dipastikan secara retrospektif.
- Tidak ada jaminan LLM bebas halusinasi/prompt injection hanya dari prompt. Sanitasi HTML melindungi rendering, bukan membuktikan kebenaran jawaban. Lakukan review grounding dengan dataset lebih besar.
- Toolchain proyek meminta Node 20.19+ atau 22.12+. Runtime Windows khusus proyek dapat dipasang melalui `powershell -File scripts/setup-node.ps1`; skrip npm memilih runtime itu otomatis. Instalasi Node sistem tidak diubah.

Rujukan implementasi: [Laravel Queue](https://laravel.com/framework/docs/12.x/queues), [DOMPurify](https://github.com/cure53/DOMPurify).
