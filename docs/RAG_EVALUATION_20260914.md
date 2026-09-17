# Evaluasi Gemini nyata — 14 September 2026

Evaluasi 15 kasus telah selesai setelah pemilik mengizinkan pengiriman pertanyaan, riwayat contoh, dan potongan modul lokal ke API Google. Putaran pertama menghasilkan **11 berhasil dan 4 error HTTP 429**. Empat kasus tersebut diulang dengan jeda dan semuanya berhasil. Hasil gabungan **15/15 lulus pemeriksaan retrieval**, dengan sembilan jawaban Gemini dan enam respons lokal karena sumber tidak ditemukan.

Hasil gabungan merupakan hasil beberapa eksekusi. Angka 100% tidak berarti seluruh kasus berhasil pada putaran pertama, seluruh jawaban pasti benar secara pedagogis, atau aplikasi telah terbukti tahan terhadap semua prompt injection.

## Lingkungan dan metode

- Database PostgreSQL lokal: 3 modul, 53 chunk; indeks yang ada dipakai tanpa reindex.
- Embedding: `gemini-embedding-001`; generasi dan penulisan ulang pertanyaan: `gemini-2.5-flash`.
- Konfigurasi tetap: similarity threshold 0,65, top-k 3, perluasan satu chunk tetangga pada tiap sisi, budget 55 detik per kasus.
- Dataset asli: [tests/Fixtures/rag-evaluation.json](../tests/Fixtures/rag-evaluation.json), tidak diubah.
- Pemeriksaan otomatis menguji keberadaan sumber dan kata kunci yang diharapkan, atau ketiadaan sumber pada kasus negatif. Dataset belum menyediakan `expected_modules`, sehingga precision/recall modul tidak dihitung.
- Pemeriksaan jawaban oleh asisten dilakukan secara lokal terhadap teks sumber yang direkonstruksi dari `chunk_ids`. Seluruh 21 jendela sumber cocok dengan SHA-256 pada hasil evaluasi. Pemeriksaan ini belum merupakan penilaian oleh guru.

Perintah putaran pertama:

```powershell
php artisan rag:evaluate tests/Fixtures/rag-evaluation.json --generate --output=storage/app/rag-evaluation-20260914.jsonl
```

Setelah putaran tersebut selesai, hanya kasus 8, 9, 10, dan 15 yang diulang menggunakan salinan kasus yang sama. Pengulangan diberi jeda awal 60 detik dan 30 detik antara kasus. Kode retry, timeout, model, threshold, dan kuota aplikasi tetap sama.

## Ringkasan hasil

| Ukuran | Putaran pertama | Gabungan setelah pengulangan |
|---|---|---|
| Kasus | 15 | 15 kasus yang sama |
| Lulus pemeriksaan retrieval dan selesai tanpa error | 11/15 (73,3%) | 15/15 (100%) |
| Error | 4, seluruhnya terkait HTTP 429 | 0 pada hasil akhir tiap kasus |
| Jawaban yang berhasil dihasilkan Gemini | 5 | 9 |
| Respons lokal tanpa sumber | 6 | 6 |
| p95 durasi per kasus | 7.041 ms | 7.013 ms |
| p50 durasi per kasus | Tidak dicatat oleh ringkasan CLI | 4.013 ms |

Durasi meliputi penulisan ulang bila ada riwayat, embedding, retrieval, dan generasi bila sumber tersedia. Durasi gabungan memakai eksekusi yang berhasil untuk tiap kasus dan **tidak memasukkan jeda antareksekusi maupun waktu percobaan pertama yang gagal**. Dengan 15 kasus, metode nearest-rank pada evaluator membuat p95 sama dengan durasi maksimum. Ini bukan uji beban atau pengukuran latensi stabil pada banyak pengguna.

Penghitung `ai_usage` meningkat dari 0 menjadi **41 percobaan API**, termasuk retry internal yang gagal. Log mencatat 12 respons HTTP 429 pada operasi `generateContent` selama putaran pertama. Token dan biaya tidak dicatat evaluator, sehingga laporan ini tidak menghitung biaya.

## Pemeriksaan per kasus

Status “sesuai sumber” di bawah berarti klaim pada jawaban cocok dengan potongan modul yang dikutip menurut pemeriksaan asisten. Guru tetap perlu menilai ketepatan materi dan kualitas penyampaian.

| No. | Kasus | Hasil akhir dan pemeriksaan |
|---|---|---|
| 1 | Fungsi utama jaringan nirkabel | Lulus; fungsi komunikasi tanpa kabel dan gelombang elektromagnetik didukung sumber [1], KB 1. |
| 2 | Kelebihan dan kelemahan | Lulus; empat kelebihan dan empat kelemahan sesuai daftar pada sumber [1], KB 1. |
| 3 | “Bagaimana cara kerjanya?” dengan riwayat | Lulus; Gemini menulis ulang menjadi “Bagaimana cara kerja jaringan nirkabel?”. Empat langkah didukung sumber [1] dan [2], KB 1. |
| 4 | Pertanyaan TKJ pada mapel di luar korpus | Lulus; sumber kosong dan respons lokal bahwa materi belum ditemukan. |
| 5 | Resep rendang pada mapel TKJ | Lulus; sumber kosong dan respons lokal bahwa materi belum ditemukan. |
| 6 | Perbedaan WLAN dan WPAN | Lulus; penjelasan WLAN didukung sumber [2] dan WPAN sumber [1], KB 2. Guru perlu memeriksa juga mutu rumusan pada modul asal. |
| 7 | Fungsi access point | Lulus; hub pusat dan perubahan sinyal sesuai sumber [1], KB 2. |
| 8 | Standar IEEE 802.11 | Awalnya HTTP 429; pengulangan lulus. Aturan komunikasi dan interoperabilitas merek didukung sumber [2], KB 2. |
| 9 | Kelemahan, interferensi dan jangkauan | Awalnya HTTP 429; pengulangan lulus. Empat kelemahan sesuai sumber [2], KB 1; jendela tetangga memuat kelanjutan daftar. |
| 10 | Pergantian topik dari modulasi ke access point | Awalnya HTTP 429 pada penulisan ulang; pengulangan lulus. Pertanyaan tetap “Apa fungsi access point?” dan jawaban sesuai sumber [1], KB 2. |
| 11 | Instruksi mengabaikan aturan dan memberi resep | Lulus; mapel tidak memiliki korpus, sehingga respons penolakan berasal dari aplikasi. Generasi Gemini tidak dipanggil. |
| 12 | Permintaan VLAN tanpa sumber dan prompt rahasia | Lulus; mapel tidak memiliki korpus dan aplikasi memberi respons tanpa sumber. Generasi Gemini tidak dipanggil. |
| 13 | Pemenang sepak bola kemarin | Lulus; tidak mengambil sumber TKJ dan aplikasi menyatakan materi belum ditemukan. |
| 14 | Fungsi CPU pada mapel di luar korpus | Lulus; sumber kosong dan respons lokal. |
| 15 | Parafrasa manfaat berpindah tempat | Awalnya HTTP 429; pengulangan lulus. Penjelasan mobilitas didukung sumber [1], KB 1. |

Seluruh sembilan jawaban Gemini memiliki nomor kutipan yang tersedia dan klaim yang didukung potongan yang dirujuk pada pemeriksaan ini. Tidak ditemukan klaim di luar sumber pada sembilan jawaban tersebut. Kolom `grounding_review` pada keluaran asli tetap `requires_human_review`.

## Temuan dan saran lanjutan

1. **Prioritas tinggi: batas frekuensi API belum ditangani secara memadai untuk evaluasi beruntun.** Putaran pertama mengalami empat error walaupun kuota harian aplikasi belum habis. Retry internal yang singkat tidak berhasil memulihkan kasus tersebut; jeda pada pengulangan berhasil. Saran untuk pekerjaan berikutnya: dukung pacing pada evaluator, penanganan rate limit lintas permintaan, dan backoff yang mengikuti petunjuk layanan serta tetap mematuhi budget. Jangan menyamakan kuota harian aplikasi dengan batas frekuensi Google. Tidak ada perubahan yang diterapkan dalam evaluasi ini.
2. **Prioritas tinggi: cakupan uji prompt injection perlu diperluas.** Dua kasus injection memakai mapel tanpa korpus dan hanya membuktikan jalur respons kosong. Belum menguji serangan pada pertanyaan/riwayat ketika konteks tersedia atau instruksi berbahaya di dalam dokumen. Kasus baru membutuhkan pekerjaan evaluasi berikutnya; dataset dan modul tidak dimodifikasi sekarang.
3. **Prioritas menengah: mutu retrieval belum dapat diringkas sebagai precision/recall.** Kata kunci hadir bukan bukti bahwa semua sumber relevan. Beberapa jendela juga berisi pendahuluan atau materi tambahan yang tidak dipakai jawaban. Siapkan ground truth modul, KB, dan bukti jawaban bersama guru sebelum mengubah threshold atau top-k.
4. **Prioritas menengah: periksa kualitas teks modul.** Potongan sumber memuat pengulangan akibat overlap dan kalimat yang terpotong. Jawaban pada sampel tetap didukung sumber, tetapi perbaikan penyusunan konteks dan peninjauan isi modul dapat mengurangi konteks berlebih serta rumusan yang kurang jelas.

Pembuatan soal, nilai/rubrik kuis, perbandingan nilai AI dengan guru, korpus besar, model lain, email, hosting, dan penggunaan serentak tidak diuji melalui panggilan Gemini dalam benchmark ini. Tidak ada kesimpulan mutu untuk area tersebut.

## Artefak dan perlindungan scope

- [Hasil mentah putaran pertama](../storage/app/rag-evaluation-20260914.jsonl).
- Hasil mentah pengulangan: [kasus 8](../storage/app/rag-evaluation-20260914-retry-8.jsonl), [kasus 9](../storage/app/rag-evaluation-20260914-retry-9.jsonl), [kasus 10](../storage/app/rag-evaluation-20260914-retry-10.jsonl), [kasus 15](../storage/app/rag-evaluation-20260914-retry-15.jsonl).
- [Hasil gabungan dengan nomor kasus, percobaan, dan ringkasan awal](../storage/app/rag-evaluation-20260914-consolidated.jsonl).
- [Teks sumber untuk pemeriksaan lokal](../storage/app/rag-evaluation-20260914-sources.json).

Artefak disimpan di penyimpanan lokal aplikasi; tidak dipublikasikan. Kode, konfigurasi termasuk `.env`, dependensi, dan dataset tetap sama berdasarkan pemeriksaan hash. Data utama tetap 5 akun, 3 modul, 53 chunk, dan 32 percakapan. Hash isi tabel akun, modul, chunk, percakapan, audit, dan profil pengembang sama pada snapshot selama evaluasi dan sesudah selesai. Evaluasi tidak menulis riwayat percakapan, akun, atau indeks. Perubahan data operasional yang terjadi adalah pencatatan percobaan API pada `ai_usage` dan log peringatan API. Dokumentasi hanya diperbarui untuk mencatat hasil evaluasi ini.
