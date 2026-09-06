# Product Requirements Document (PRD)
## Sistem Manajemen Modul & Jobsheet TKJ dengan RAG Chatbot

**Versi:** 1.0
**Tanggal:** 25 Juli 2026
**Status:** Draft

---

## 1. Latar Belakang

Guru jurusan Teknik Komputer dan Jaringan (TKJ) membutuhkan media pembelajaran digital untuk mendistribusikan modul dan jobsheet praktikum kepada siswa. Selama ini distribusi materi dan sesi tanya-jawab dilakukan secara manual/tatap muka, sehingga siswa tidak memiliki akses cepat untuk bertanya seputar materi di luar jam pelajaran.

Sistem ini dibangun untuk menjembatani hal tersebut dengan menyediakan platform upload materi berbasis web, dilengkapi chatbot berbasis **Retrieval-Augmented Generation (RAG)** yang mampu menjawab pertanyaan siswa berdasarkan isi modul/jobsheet yang telah diupload guru.

## 2. Tujuan Produk

1. Menyediakan media penyimpanan dan distribusi modul/jobsheet digital yang terpusat.
2. Memberikan akses belajar mandiri bagi siswa melalui chatbot yang menjawab berdasarkan materi resmi dari guru (bukan jawaban umum dari internet).
3. Mengurangi ketergantungan siswa bertanya langsung ke guru untuk hal-hal dasar yang sudah tercakup dalam modul.

## 3. Target Pengguna

| Role | Deskripsi |
|---|---|
| Guru | Mengelola akun, mengupload modul/jobsheet, memantau aktivitas |
| Siswa | Mengakses modul, berdiskusi dengan chatbot seputar materi |

## 4. Ruang Lingkup (Scope)

### 4.1 In Scope
- Autentikasi & manajemen akun guru dan siswa (role-based)
- CRUD modul dan jobsheet (upload, edit, hapus, kategori mata pelajaran)
- Ekstraksi teks otomatis dari file PDF dan Word (.docx)
- Chatbot RAG untuk diskusi materi berbasis dokumen yang diupload
- Riwayat percakapan chatbot per siswa

### 4.2 Out of Scope
- Bantuan penyusunan dokumen skripsi/proposal (Bab I–V)
- OCR untuk PDF hasil scan gambar (dibahas terpisah jika dibutuhkan, di luar estimasi awal)
- Fitur nilai/rapor, absensi, atau LMS lengkap (kuis terjadwal, forum diskusi antar siswa, dsb.)
- Aplikasi mobile native (web-based responsive saja)

## 5. Batasan Sistem (Constraints)

Batasan berikut ditetapkan agar ekspektasi pengembangan dan penggunaan sistem jelas sejak awal:

| Kategori | Batasan |
|---|---|
| Format dokumen | Hanya menerima PDF dan .docx berbasis teks asli (bukan hasil scan gambar), maksimal ukuran file per upload (mis. 10 MB) |
| Jumlah dokumen | Dioptimalkan untuk skala kecil-menengah (± 20–30 modul); performa pencarian belum diuji untuk skala ratusan dokumen |
| Bahasa | Materi dan pertanyaan chatbot dalam Bahasa Indonesia; akurasi tidak dijamin untuk campuran bahasa asing/istilah non-standar |
| Cakupan jawaban | Chatbot hanya menjawab dari konteks modul yang sudah diindeks, tidak menjawab pertanyaan di luar cakupan materi (mis. tugas personal, curhat, topik umum) |
| Konkurensi pengguna | Dirancang untuk penggunaan skala kelas/sekolah (puluhan pengguna bersamaan), bukan skala enterprise |
| Ketergantungan pihak ketiga | Fitur embedding dan chatbot bergantung pada ketersediaan dan kuota API Gemini; sistem tidak berfungsi penuh jika API down atau kuota habis |
| Konten multimedia | Gambar, diagram topologi, dan tabel kompleks dalam dokumen berpotensi tidak terbaca sempurna oleh proses ekstraksi otomatis |
| Infrastruktur | Diasumsikan hosting shared/VPS standar; tidak dirancang untuk load balancing multi-server pada versi awal |

## 6. Kebutuhan Fungsional

### 6.1 Modul Autentikasi & Role
- FR-1: Sistem menyediakan login terpisah untuk role guru dan siswa
- FR-2: Guru dapat mengelola (tambah/edit/nonaktifkan) akun siswa
- FR-3: Setiap role memiliki akses menu yang berbeda sesuai hak akses

### 6.2 Modul Manajemen Materi (Guru)
- FR-4: Guru dapat mengupload modul dan jobsheet dalam format PDF atau .docx
- FR-5: Guru dapat mengelompokkan materi berdasarkan mata pelajaran/topik (mis. Subnetting, VLAN, Routing)
- FR-6: Sistem melakukan ekstraksi teks otomatis dari file yang diupload
- FR-7: Sistem melakukan chunking dan indexing (embedding) otomatis setelah file berhasil diekstrak
- FR-8: Guru dapat melihat status indexing (berhasil/gagal) dari tiap dokumen
- FR-9: Guru dapat mengedit atau menghapus materi yang sudah diupload (termasuk menghapus index terkait)


### 6.3 Modul Chatbot RAG (Siswa)
- FR-10: Siswa dapat mengajukan pertanyaan bebas terkait materi melalui antarmuka chat
- FR-11: Sistem mencari potongan materi (chunk) yang paling relevan dengan pertanyaan menggunakan pencarian kemiripan vektor (cosine similarity)
- FR-12: Sistem menyusun jawaban menggunakan LLM berdasarkan konteks chunk yang ditemukan (bukan pengetahuan umum LLM)
- FR-13: Jika tidak ditemukan konteks relevan, chatbot memberi tahu bahwa materi tidak tersedia, alih-alih memberi jawaban yang tidak berdasar
- FR-14: Sistem menyimpan riwayat percakapan siswa untuk ditinjau ulang

### 6.4 Modul Umum
- FR-15: Sistem menampilkan daftar modul/jobsheet yang dapat difilter per mata pelajaran
- FR-16: Siswa dapat mengunduh file asli modul/jobsheet

## 7. Kebutuhan Non-Fungsional

### 7.1 Keamanan (Security)

| Area | Kebutuhan |
|---|---|
| Autentikasi | Password di-hash (bcrypt/argon2 bawaan Laravel), rate limiting pada endpoint login untuk mencegah brute force |
| Otorisasi | Middleware role-based access control (RBAC) di setiap route — guru tidak bisa akses endpoint siswa dan sebaliknya |
| Proteksi form & request | CSRF token di semua form, validasi input di sisi server (bukan hanya client-side) |
| Upload file | Validasi MIME type asli (bukan hanya ekstensi), pembatasan ukuran file, penyimpanan file di luar direktori publicly-executable, penamaan file di-randomize/hash untuk mencegah path traversal |
| Data sensitif | API key Gemini disimpan di `.env`, tidak pernah di-expose ke frontend/response API |
| Injeksi | Query builder/Eloquent ORM (parameterized query) untuk mencegah SQL Injection, escaping output untuk mencegah XSS |
| Chatbot | Sanitasi input pertanyaan siswa sebelum diproses, pembatasan panjang teks untuk mencegah prompt injection berlebihan ke LLM |
| Audit | Logging aktivitas penting (upload, hapus modul, login gagal) untuk keperluan audit trail |

### 7.2 Performa

| Area | Target/Kebutuhan |
|---|---|
| Respons chatbot | Idealnya < 5 detik per pertanyaan (termasuk waktu embedding + retrieval + generation) |
| Query database | Indexing pada kolom yang sering di-query (mis. `module_id`, `role`, `mapel`) |
| Proses embedding | Dijalankan secara asynchronous/queue (Laravel Queue) saat upload dokumen besar, agar tidak memblokir request utama |
| Caching | Cache hasil pencarian/pertanyaan yang sering diulang (opsional, mis. Redis) untuk mengurangi beban API embedding berulang |
| Pagination | Semua listing data (modul, riwayat chat) menggunakan pagination, bukan load seluruh data sekaligus |

### 7.3 Skalabilitas

| Area | Pendekatan |
|---|---|
| Struktur data | Desain tabel modular (`modules`, `module_chunks`, `chat_histories`) sehingga mudah ditambah fitur/mapel baru tanpa restrukturisasi besar |
| Proses berat | Ekstraksi & embedding dokumen dipisah dari request HTTP utama (queue/job), agar penambahan volume dokumen tidak membebani response time |
| Vector search | Jika volume chunk bertambah signifikan, struktur data disiapkan agar dapat bermigrasi ke vector database khusus (mis. pgvector, atau layanan vector DB eksternal) tanpa mengubah alur aplikasi secara drastis |
| Modularitas kode | Pemisahan logic ke service/repository layer (bukan menumpuk semua di controller) agar mudah dikembangkan/diaudit ke depannya |

### 7.4 Keandalan & Kesiapan Produksi (Reliability)

| Area | Kebutuhan |
|---|---|
| Error handling | Try-catch pada semua pemanggilan API eksternal (Gemini), dengan pesan fallback yang jelas ke pengguna jika API gagal/timeout |
| Ketersediaan data asli | Dokumen asli tetap tersimpan meski hanya teks yang diindeks, sehingga tidak ada kehilangan data sumber |
| Retry mechanism | Percobaan ulang otomatis (dengan batas maksimal) untuk proses embedding yang gagal karena gangguan jaringan/API sementara |
| Environment terpisah | Konfigurasi terpisah untuk local/staging/production (`.env` per environment), tidak ada kredensial hardcoded di source code |
| Backup | Rekomendasi backup database berkala (minimal harian) untuk mencegah kehilangan data modul dan riwayat chat |

### 7.5 Kompatibilitas & Usability

| Area | Kebutuhan |
|---|---|
| Responsive | Dapat diakses dengan baik di desktop dan mobile browser |
| Kompatibilitas browser | Diuji minimal pada Chrome dan Firefox versi terbaru |
| Aksesibilitas dasar | Label form yang jelas, pesan error yang informatif (bukan pesan error teknis mentah ke pengguna akhir) |

### 7.6 Best Practices Pengembangan Backend

- Mengikuti struktur MVC standar Laravel, dengan business logic dipisah ke Service Class, bukan ditumpuk di Controller
- Penggunaan Form Request untuk validasi input, bukan validasi inline di controller
- Konsisten menggunakan migration & seeder untuk struktur database (tidak ada perubahan skema manual langsung di database produksi)
- Penulisan kode mengikuti PSR-12 coding standard
- Environment variable untuk semua konfigurasi sensitif (API key, DB credential), tidak ada nilai hardcoded
- Unit/feature testing minimal untuk fungsi kritis (autentikasi, upload, proses retrieval) sebelum serah terima
- Dokumentasi endpoint API (jika chatbot diakses via API terpisah/AJAX) agar mudah dipelihara ke depannya

## 8. Alur Sistem (High-Level)

1. **Alur Upload & Indexing** — Guru upload dokumen → sistem ekstrak teks → chunking → embedding → simpan ke database vektor.
2. **Alur Tanya-Jawab** — Siswa mengajukan pertanyaan → sistem embed pertanyaan → cari chunk relevan → kirim konteks + pertanyaan ke LLM → tampilkan jawaban ke siswa.

*(Detail lengkap tersedia pada sequence diagram terpisah: `rag_chatbot_flow.mermaid`)*

## 9. Rencana Teknis (Tech Stack Usulan)

| Komponen | Teknologi |
|---|---|
| Backend | Laravel (PHP) |
| Database | MySQL / PostgreSQL (dengan pgvector jika tersedia) |
| Ekstraksi PDF | smalot/pdfparser |
| Ekstraksi Word | PhpOffice/PhpWord |
| Embedding & Generation | Gemini API |
| Frontend | Blade / Vue-Blade hybrid sesuai kebutuhan |

## 10. Struktur Data Awal (Draft)

- `users` (id, name, email, password, role: guru/siswa)
- `modules` (id, guru_id, judul, mapel, file_path, status_indexing)
- `module_chunks` (id, module_id, chunk_text, embedding_vector)
- `chat_histories` (id, siswa_id, pertanyaan, jawaban, referensi_chunk_id, created_at)

## 11. Kriteria Penerimaan (Acceptance Criteria)

- Guru dapat mengupload modul PDF dan Word, dan sistem berhasil mengekstrak isinya tanpa intervensi manual (untuk dokumen berbasis teks, bukan hasil scan)
- Siswa dapat mengajukan pertanyaan dan menerima jawaban yang relevan dengan isi modul yang sudah diupload
- Chatbot tidak memberikan jawaban di luar konteks modul yang tersedia
- Guru dan siswa memiliki akses menu yang sesuai dengan role masing-masing
- Semua route dilindungi middleware autentikasi & role, tidak dapat diakses oleh pengguna tanpa login atau role yang salah
- Sistem tetap menampilkan pesan error yang wajar (bukan crash/500 mentah) saat API eksternal (Gemini) gagal merespons
- Proses upload & indexing dokumen besar tidak membuat aplikasi freeze/timeout bagi pengguna lain yang sedang mengakses sistem

## 12. Risiko & Catatan

- Dokumen PDF hasil scan (gambar) tidak dapat diekstrak tanpa OCR tambahan — perlu konfirmasi jenis dokumen ke pengguna akhir sebelum upload.
- Tabel dan diagram dalam modul (mis. topologi jaringan) berpotensi tidak terbaca sempurna oleh proses ekstraksi otomatis; perlu pengecekan manual pada dokumen kompleks.
- Akurasi jawaban chatbot bergantung pada kualitas chunking; perlu pengujian iteratif sebelum serah terima akhir.

---
*Dokumen ini adalah dasar pengembangan sistem dan dapat direvisi sesuai kebutuhan lanjutan dari pengguna/klien.*
