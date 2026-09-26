<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;

$phpWord = new PhpWord();

// Define styles
$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16, 'color' => '008546', 'name' => 'Arial'], ['spaceAfter' => 180, 'alignment' => Jc::CENTER]);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 13, 'color' => '1E293B', 'name' => 'Arial'], ['spaceBefore' => 200, 'spaceAfter' => 120]);
$phpWord->addTitleStyle(3, ['bold' => true, 'size' => 11, 'color' => '334155', 'name' => 'Arial'], ['spaceBefore' => 140, 'spaceAfter' => 80]);

$bodyStyle = ['size' => 10.5, 'name' => 'Arial', 'color' => '1E293B'];
$boldStyle = ['bold' => true, 'size' => 10.5, 'name' => 'Arial', 'color' => '1E293B'];
$italicStyle = ['italic' => true, 'size' => 10, 'name' => 'Arial', 'color' => '475569'];
$pStyle = ['spaceAfter' => 120, 'lineHeight' => 1.25];

$section = $phpWord->addSection([
    'marginTop' => Converter::cmToTwip(2.5),
    'marginBottom' => Converter::cmToTwip(2.5),
    'marginLeft' => Converter::cmToTwip(2.5),
    'marginRight' => Converter::cmToTwip(2.5),
]);

// Header
$section->addText('SMK NEGERI 1 KINALI', ['bold' => true, 'size' => 14, 'color' => '008546', 'name' => 'Arial'], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
$section->addText('KOMPETENSI KEAHLIAN TEKNIK KOMPUTER DAN JARINGAN (TKJ)', ['bold' => true, 'size' => 11, 'color' => '475569', 'name' => 'Arial'], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
$section->addText('MODUL AJAR KEGIATAN BELAJAR 4 (KB 4)', ['bold' => true, 'size' => 12, 'color' => '0F172A', 'name' => 'Arial'], ['alignment' => Jc::CENTER, 'spaceAfter' => 180]);

// Title
$section->addTitle('KEAMANAN JARINGAN DAN KONFIGURASI FIREWALL FILTERING PADA ROUTER GATEWAY', 1);

// Meta Table
$table = $section->addTable(['borderSize' => 6, 'borderColor' => 'CBD5E1', 'alignment' => Jc::CENTER]);
$table->addRow();
$table->addCell(Converter::cmToTwip(4))->addText('Mata Pelajaran', $boldStyle);
$table->addCell(Converter::cmToTwip(11))->addText('Teknik Komputer dan Jaringan (Administrasi Infrastruktur Jaringan)', $bodyStyle);
$table->addRow();
$table->addCell(Converter::cmToTwip(4))->addText('Kelas / Fase', $boldStyle);
$table->addCell(Converter::cmToTwip(11))->addText('XII (Dua Belas) / Fase F - SMK', $bodyStyle);
$table->addRow();
$table->addCell(Converter::cmToTwip(4))->addText('Alokasi Waktu', $boldStyle);
$table->addCell(Converter::cmToTwip(11))->addText('4 Jam Pelajaran (4 x 45 Menit)', $bodyStyle);

$section->addTextBreak(1);

// TP
$section->addTitle('A. TUJUAN PEMBELAJARAN (TP)', 2);
$section->addText('Setelah mempelajari modul Kegiatan Belajar 4 (KB 4) ini, peserta didik diharapkan mampu:', $bodyStyle, $pStyle);
$section->addListItem('Menjelaskan prinsip dasar keamanan jaringan informasi (CIA Triad: Confidentiality, Integrity, Availability).', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Mengidentifikasi berbagai jenis ancaman dan serangan keamanan jaringan (Port Scanning, DoS/DDoS, SYN Flood Attack).', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Menganalisis konsep arsitektur firewall dan mekanisme packet filtering.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Membedakan alur kerja rantai pemrosesan paket (Chains: Input, Forward, Output) pada router gateway.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Mengonfigurasi aturan filtering firewall (Action: Accept, Drop, Reject) untuk mengamankan jaringan lokal dan router gateway.', 0, $bodyStyle, null, $pStyle);

// Uraian Materi
$section->addTitle('B. URAIAN MATERI PEMBELAJARAN', 2);

$section->addTitle('1. Prinsip Dasar Keamanan Jaringan', 3);
$section->addText('Keamanan jaringan (network security) merupakan seperangkat kebijakan, proses, dan teknologi yang dirancang untuk melindungi integritas, kerahasiaan, dan aksesibilitas infrastruktur jaringan serta data di dalamnya. Dalam dunia keamanan informasi, terdapat tiga fondasi pilar utama yang dikenal sebagai CIA Triad:', $bodyStyle, $pStyle);

$section->addListItem('Confidentiality (Kerahasiaan): Memastikan bahwa informasi dan data hanya dapat diakses oleh pihak yang memiliki hak otorisasi resmi. Pencegahan kebocoran data dilakukan menggunakan enkripsi (seperti IPsec, TLS/HTTPS, SSH).', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Integrity (Keutuhan): Menjamin bahwa data yang dikirimkan tetap akurat, konsisten, dan tidak diubah, dimanipulasi, atau dirusak oleh pihak yang tidak sah selama proses transmisi (dapat diverifikasi melalui fungsi hash seperti SHA-256).', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Availability (Ketersediaan): Menjamin bahwa layanan jaringan, server, dan sumber daya sistem dapat diakses secara andal dan tepat waktu saat dibutuhkan oleh pengguna yang berhak, terhindar dari pemadaman akibat serangan Denial of Service.', 0, $bodyStyle, null, $pStyle);

$section->addText('Selain ketiga pilar utama tersebut, prinsip pendukung penting meliputi Authentication (pembuktian identitas pengirim/pengguna) dan Non-Repudiation (penjaminan bahwa pihak yang melakukan transaksi atau pengiriman data tidak dapat menyangkal perbuatannya).', $bodyStyle, $pStyle);

$section->addTitle('2. Identifikasi Ancaman dan Serangan Jaringan', 3);
$section->addText('Sebagai administrator jaringan TKJ, mengenali vektor serangan adalah kunci dalam menyusun sistem proteksi:', $bodyStyle, $pStyle);
$section->addListItem('Reconnaissance / Port Scanning: Tahap pengumpulan informasi oleh penyerang untuk memetakan port yang terbuka, layanan (service), dan versi sistem operasi pada target (umumnya memakai tool seperti Nmap).', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Denial of Service (DoS) dan DDoS: Upaya melumpuhkan router atau server target dengan membanjiri trafik lalu lintas data sampah dalam volume yang sangat besar hingga sistem kehabisan memori atau bandwidth.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('SYN Flood Attack: Jenis serangan DoS pada lapisan transport TCP di mana penyerang mengirimkan bertubi-tubi paket TCP SYN tanpa pernah menyelesaikan three-way handshake (tidak mengirimkan ACK balasan). Akibatnya, antrean koneksi (backlog queue) pada target menjadi penuh dan menolak koneksi baru dari pengguna yang sah.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Man-in-the-Middle (MitM) dan ARP Spoofing: Penyerang menyisipkan diri di antara dua entitas yang saling berkomunikasi dengan memalsukan tabel ARP untuk mencuri atau memodifikasi percakapan data.', 0, $bodyStyle, null, $pStyle);

$section->addTitle('3. Konsep dan Mekanisme Kerja Firewall', 3);
$section->addText('Firewall adalah perangkat keamanan yang memonitor dan menyaring lalu lintas jaringan masuk dan keluar berdasarkan aturan keamanan yang telah ditetapkan sebelumnya. Firewall bertindak sebagai gerbang pembatas antara jaringan internal sekolah/kantor yang tepercaya (trusted) dengan jaringan publik/Internet yang tidak tepercaya (untrusted).', $bodyStyle, $pStyle);
$section->addText('Berdasarkan cara kerjanya, firewall dibedakan menjadi Stateless Packet Filtering (memeriksa paket secara independen berdasarkan header IP dan Port) serta Stateful Packet Inspection (SPI) yang lebih canggih karena mencatat status percakapan koneksi (Connection State) yang aktif, seperti New, Established, Related, dan Invalid.', $bodyStyle, $pStyle);

$section->addTitle('4. Alur Rantai Pemrosesan Paket (Firewall Chains)', 3);
$section->addText('Pada router gateway modern (seperti Linux iptables maupun MikroTik RouterOS), filtering dikelompokkan ke dalam tiga rantai utama (Chains):', $bodyStyle, $pStyle);
$section->addListItem('Chain INPUT: Menangani seluruh paket data yang ditujukan langsung ke router itu sendiri sebagai perangkat tujuan akhir. Contoh: Trafik saat teknisi mengakses router melalui Winbox, WebFig (HTTP port 80 / HTTPS 443), atau SSH (port 22).', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Chain FORWARD: Menangani seluruh paket data yang hanya melintasi router dari satu antarmuka (interface) menuju antarmuka lainnya tanpa ditujukan ke router itu sendiri. Contoh: Komputer siswa di jaringan LAN mengakses website di internet melalui router gateway.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Chain OUTPUT: Menangani paket data yang dibangkitkan dan berasal dari dalam router itu sendiri dan dikirimkan keluar. Contoh: Router melakukan ping diagnostik ke DNS Google (8.8.8.8) atau sinkronisasi waktu NTP.', 0, $bodyStyle, null, $pStyle);

$section->addTitle('5. Tindakan Penyaringan (Firewall Actions)', 3);
$section->addText('Ketika paket data cocok (match) dengan kriteria kueri suatu aturan filter, router akan menjalankan tindakan yang ditentukan:', $bodyStyle, $pStyle);
$section->addListItem('Action ACCEPT: Mengizinkan paket data untuk melanjutkan perjalanan ke tujuan.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Action DROP: Menolak dan membuang paket data secara diam-diam tanpa memberikan pemberitahuan atau pesan balasan error kepada pengirim. Tindakan ini sangat efisien untuk meredam serangan scanning dan DoS karena tidak membebani router untuk membuat paket balasan.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Action REJECT: Menolak paket data sekaligus secara eksplisit mengirimkan paket pesan kesalahan (seperti ICMP Destination Unreachable atau TCP Reset) kembali kepada pengirim.', 0, $bodyStyle, null, $pStyle);

$section->addTitle('6. Langkah Strategis Konfigurasi Firewall Filtering Router', 3);
$section->addText('Aturan praktis (best practice) dalam membangun firewall filtering yang tangguh meliputi urutan prioritas:', $bodyStyle, $pStyle);
$section->addListItem('Aturan 1 (Drop Invalid): Segera buang paket data dengan connection-state=invalid pada chain input dan forward.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Aturan 2 (Accept Established dan Related): Izinkan semua paket data yang merupakan kelanjutan dari koneksi yang sudah sah.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Aturan 3 (Proteksi Router Management): Hanya izinkan akses port SSH (TCP 22) dan Winbox (TCP 8291) dari IP subnet manajemen jaringan sekolah, dan drop akses manajemen dari antarmuka publik/WAN.', 0, $bodyStyle, null, $pStyle);
$section->addListItem('Aturan 4 (Drop All Unmatched): Di akhir rantai konfigurasi, terapkan kebijakan default drop untuk seluruh trafik yang tidak terdefinisi secara eksplisit.', 0, $bodyStyle, null, $pStyle);

// Rangkuman
$section->addTitle('C. RANGKUMAN', 2);
$section->addText('1. Keamanan jaringan berpusat pada CIA Triad: Kerahasiaan (Confidentiality), Keutuhan (Integrity), dan Ketersediaan (Availability).', $bodyStyle, $pStyle);
$section->addText('2. Serangan SYN Flood memanfaatkan kerentanan mekanisme TCP three-way handshake untuk menghabiskan sumber daya koneksi target.', $bodyStyle, $pStyle);
$section->addText('3. Rantai firewall terdiri dari Input (menuju router), Forward (melintasi router), dan Output (keluar dari router).', $bodyStyle, $pStyle);
$section->addText('4. Perbedaan mendasar Action DROP dan REJECT adalah: DROP membuang paket tanpa respon, sedangkan REJECT menolak paket disertai kiriman pesan ICMP error kepada pengirim.', $bodyStyle, $pStyle);
$section->addText('5. SSH menggunakan port standar 22 dan komunikasi terenkripsi sehingga sangat disarankan sebagai metode administrasi jarak jauh router menggantikan Telnet.', $bodyStyle, $pStyle);

// Glosarium
$section->addTitle('D. GLOSARIUM', 2);
$section->addText('- CIA Triad: Model standar acuan keamanan informasi (Confidentiality, Integrity, Availability).', $bodyStyle, $pStyle);
$section->addText('- Firewall: Sistem keamanan yang mengatur dan menyaring lalu lintas keluar-masuk jaringan.', $bodyStyle, $pStyle);
$section->addText('- Chain: Rantai atau tahapan logika klasifikasi pemrosesan paket data pada sistem firewall.', $bodyStyle, $pStyle);
$section->addText('- SYN Flood: Bentuk serangan DoS berbasis eksploitasi antrean handshake protokol TCP.', $bodyStyle, $pStyle);
$section->addText('- SSH (Secure Shell): Protokol jaringan kriptografi untuk komunikasi data dan administrasi remote yang aman melalui port TCP 22.', $bodyStyle, $pStyle);

$outputPath = dirname(__DIR__) . '/storage/app/private/modules/kb4_keamanan_jaringan.docx';
if (!is_dir(dirname($outputPath))) {
    mkdir(dirname($outputPath), 0777, true);
}

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($outputPath);

echo "SUCCESS: File DOCX berhasil dibuat di " . $outputPath . " (" . filesize($outputPath) . " bytes)\n";
