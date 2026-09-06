<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportActivityService
{
    /**
     * Generate file Excel (.xlsx) Rekapitulasi Aktivitas Pembelajaran Siswa
     */
    public function export(): StreamedResponse
    {
        Carbon::setLocale('id');

        $spreadsheet = new Spreadsheet();

        // 1. Terapkan Font Global: Times New Roman 12pt
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        // Ambil Data Guru & Waktu
        $guru = Auth::user();
        $namaGuru = $guru ? $guru->name : 'Rudi Putra, S.Pd.';
        $nipGuru = '19850412 201101 1 003';
        $waktuCetak = Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB';
        $tanggalPengesahan = Carbon::now()->translatedFormat('d F Y');

        // Ambil Data Siswa beserta Relasi Chat
        $siswas = User::where('role', 'siswa')
            ->with(['chatHistories.referensiChunk.module'])
            ->orderBy('name', 'asc')
            ->get();

        $totalSiswa = $siswas->count();
        $siswaAktifCount = 0;
        $totalPertanyaanAll = 0;

        foreach ($siswas as $s) {
            $pertanyaanCount = $s->chatHistories->where('pertanyaan', '!=', '[LATIHAN_SOAL]')->count();
            if ($pertanyaanCount > 0) {
                $siswaAktifCount++;
            }
            $totalPertanyaanAll += $pertanyaanCount;
        }

        $persenAktif = $totalSiswa > 0 ? round(($siswaAktifCount / $totalSiswa) * 100, 1) : 0;
        $rataRataTanya = $totalSiswa > 0 ? round($totalPertanyaanAll / $totalSiswa, 2) : 0;

        // =====================================================================
        // SHEET 1: REKAPITULASI KEAKTIFAN SISWA
        // =====================================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Rekap Keaktifan Siswa');
        $sheet1->setShowGridLines(true);

        // Styling Palette
        $greenHeaderBg = '107C41'; // Classic Excel Academic Green
        $softGreenBg = 'E8F5E9';
        $lightGrayBg = 'F4F4F5';
        $borderGray = 'A1A1AA';

        // Border styles
        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => $borderGray],
                ],
            ],
        ];

        // --- ROW 1 - 3: KOP LAPORAN RESMI ---
        $sheet1->mergeCells('A1:I1');
        $sheet1->setCellValue('A1', 'PEMERINTAH PROVINSI SUMATERA BARAT • DINAS PENDIDIKAN');
        $sheet1->getStyle('A1')->getFont()->setBold(true);
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($softGreenBg);

        $sheet1->mergeCells('A2:I2');
        $sheet1->setCellValue('A2', 'SMK NEGERI 1 KINALI — KABUPATEN PASAMAN BARAT');
        $sheet1->getStyle('A2')->getFont()->setBold(true);
        $sheet1->getStyle('A2')->getFont()->getColor()->setRGB('107C41');
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($softGreenBg);

        $sheet1->mergeCells('A3:I3');
        $sheet1->setCellValue('A3', 'LAPORAN REKAPITULASI AKTIVITAS BELAJAR & INTERAKSI SISWA DENGAN E-MODUL RAG AI');
        $sheet1->getStyle('A3')->getFont()->setBold(true);
        $sheet1->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('52525B'));

        // --- ROW 5 - 8: METADATA RESMI SEKOLAH & GURU ---
        $metadata = [
            ['Nama Sekolah', ': SMK Negeri 1 Kinali', 'Guru Pengampu', ": {$namaGuru} (Akun Login)"],
            ['NPSN / ID Sekolah', ': 10304384', 'NIP / ID Guru', ": {$nipGuru}"],
            ['Konsentrasi Keahlian', ': Teknik Komputer dan Jaringan (TKJ)', 'Mata Pelajaran', ': Administrasi Infrastruktur & Jaringan Nirkabel'],
            ['Waktu Cetak Laporan', ": {$waktuCetak}", 'Arsitektur AI', ': Google Gemini 2.5 Flash (Strict Grounded RAG)'],
        ];

        $r = 5;
        foreach ($metadata as $row) {
            $sheet1->setCellValue("A{$r}", $row[0]);
            $sheet1->mergeCells("B{$r}:D{$r}");
            $sheet1->setCellValue("B{$r}", $row[1]);

            $sheet1->setCellValue("E{$r}", $row[2]);
            $sheet1->mergeCells("F{$r}:I{$r}");
            $sheet1->setCellValue("F{$r}", $row[3]);

            $sheet1->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet1->getStyle("A{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($lightGrayBg);

            $sheet1->getStyle("E{$r}")->getFont()->setBold(true);
            $sheet1->getStyle("E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($lightGrayBg);

            $sheet1->getStyle("A{$r}:I{$r}")->applyFromArray($thinBorder);
            $r++;
        }

        // --- ROW 10 - 11: MINI DASHBOARD KPI KELAS ---
        $sheet1->mergeCells('A10:B10')->setCellValue('A10', 'TOTAL SISWA TERDAFTAR');
        $sheet1->mergeCells('C10:D10')->setCellValue('C10', 'SISWA AKTIF BELAJAR');
        $sheet1->mergeCells('E10:F10')->setCellValue('E10', 'TOTAL PERTANYAAN AI');
        $sheet1->mergeCells('G10:I10')->setCellValue('G10', 'RATA-RATA TANYA / SISWA');

        $sheet1->getStyle('A10:I10')->getFont()->setBold(true);
        $sheet1->getStyle('A10:I10')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A10:I10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A10:I10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1B5E20');
        $sheet1->getStyle('A10:I10')->applyFromArray($thinBorder);

        $sheet1->mergeCells('A11:B11')->setCellValue('A11', "{$totalSiswa} Siswa");
        $sheet1->mergeCells('C11:D11')->setCellValue('C11', "{$siswaAktifCount} Siswa ({$persenAktif}%)");
        $sheet1->mergeCells('E11:F11')->setCellValue('E11', "{$totalPertanyaanAll} Pertanyaan");
        $sheet1->mergeCells('G11:I11')->setCellValue('G11', "{$rataRataTanya} Tanya / Siswa");

        $sheet1->getStyle('A11:I11')->getFont()->setBold(true);
        $sheet1->getStyle('A11:I11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A11:I11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($softGreenBg);
        $sheet1->getStyle('A11:I11')->applyFromArray($thinBorder);

        // --- ROW 13: HEADER TABEL DATA SISWA ---
        $tableHeaders = [
            'A' => 'NO',
            'B' => 'NIS / USERNAME',
            'C' => 'NAMA LENGKAP SISWA',
            'D' => 'KELAS',
            'E' => 'MODUL DIAKSES',
            'F' => 'TANYA CHATBOT',
            'G' => 'LATIHAN SOAL AI',
            'H' => 'AKTIF TERAKHIR',
            'I' => 'STATUS PARTISIPASI',
        ];

        foreach ($tableHeaders as $col => $title) {
            $sheet1->setCellValue("{$col}13", $title);
        }

        $sheet1->getStyle('A13:I13')->getFont()->setBold(true);
        $sheet1->getStyle('A13:I13')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A13:I13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('C13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet1->getStyle('A13:I13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($greenHeaderBg);
        $sheet1->getStyle('A13:I13')->applyFromArray($thinBorder);

        // --- ROW 14+: DATA SISWA ---
        $rowIdx = 14;
        $no = 1;

        foreach ($siswas as $siswa) {
            $chats = $siswa->chatHistories;
            $tanyaCount = $chats->where('pertanyaan', '!=', '[LATIHAN_SOAL]')->count();
            $kuisCount = $chats->where('pertanyaan', '[LATIHAN_SOAL]')->count();

            // Kumpulkan modul yang diakses
            $modulList = [];
            foreach ($chats as $c) {
                if ($c->referensiChunk && $c->referensiChunk->module) {
                    $kode = $c->referensiChunk->module->kb_nomor ?: $c->referensiChunk->module->judul;
                    if (!in_array($kode, $modulList)) {
                        $modulList[] = $kode;
                    }
                }
            }
            $modulText = count($modulList) > 0 ? implode(', ', $modulList) : '-';

            // Waktu interaksi terakhir
            $lastChat = $chats->sortByDesc('created_at')->first();
            $aktifTerakhir = $lastChat ? $lastChat->created_at->translatedFormat('d/m/Y H:i') : 'Belum Pernah';

            // Kategori partisipasi
            if ($tanyaCount >= 10) {
                $statusPartisipasi = 'Sangat Aktif';
            } elseif ($tanyaCount >= 5) {
                $statusPartisipasi = 'Aktif';
            } elseif ($tanyaCount > 0) {
                $statusPartisipasi = 'Cukup Aktif';
            } else {
                $statusPartisipasi = 'Perlu Dorongan';
            }

            $sheet1->setCellValue("A{$rowIdx}", $no);
            $sheet1->setCellValue("B{$rowIdx}", $siswa->email);
            $sheet1->setCellValue("C{$rowIdx}", $siswa->name);
            $sheet1->setCellValue("D{$rowIdx}", 'XII TKJ');
            $sheet1->setCellValue("E{$rowIdx}", $modulText);
            $sheet1->setCellValue("F{$rowIdx}", "{$tanyaCount} kali");
            $sheet1->setCellValue("G{$rowIdx}", $kuisCount > 0 ? "{$kuisCount} kali" : 'Belum Ada');
            $sheet1->setCellValue("H{$rowIdx}", $aktifTerakhir);
            $sheet1->setCellValue("I{$rowIdx}", $statusPartisipasi);

            // Text alignment
            $sheet1->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("B{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("D{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("E{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("F{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("G{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("H{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("I{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Alternate row background
            if ($rowIdx % 2 == 1) {
                $sheet1->getStyle("A{$rowIdx}:I{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
            }

            $sheet1->getStyle("A{$rowIdx}:I{$rowIdx}")->applyFromArray($thinBorder);

            $rowIdx++;
            $no++;
        }

        // --- PENGESAHAN / TANDA TANGAN GURU ---
        $sigRow = $rowIdx + 2;
        $sheet1->mergeCells("F{$sigRow}:I{$sigRow}")->setCellValue("F{$sigRow}", "Kinali, {$tanggalPengesahan}");
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sigRow++;
        $sheet1->mergeCells("F{$sigRow}:I{$sigRow}")->setCellValue("F{$sigRow}", "Guru Pengampu Mata Pelajaran TKJ,");
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getFont()->setBold(true);

        $sigRow += 4;
        $sheet1->mergeCells("F{$sigRow}:I{$sigRow}")->setCellValue("F{$sigRow}", $namaGuru);
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getFont()->setBold(true);
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getFont()->setUnderline(true);

        $sigRow++;
        $sheet1->mergeCells("F{$sigRow}:I{$sigRow}")->setCellValue("F{$sigRow}", "NIP. {$nipGuru}");
        $sheet1->getStyle("F{$sigRow}:I{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto size columns for Sheet 1
        foreach (range('A', 'I') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        }

        // =====================================================================
        // SHEET 2: LOG DETAIL TRANSKRIP CHATBOT AI
        // =====================================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Log Transkrip Chatbot AI');
        $sheet2->setShowGridLines(true);

        $sheet2->mergeCells('A1:G1');
        $sheet2->setCellValue('A1', 'TRANSKRIP LENGKAP INTERAKSI CHATBOT AI SISWA — SMK NEGERI 1 KINALI');
        $sheet2->getStyle('A1')->getFont()->setBold(true);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($softGreenBg);

        $sheet2Headers = [
            'A' => 'NO',
            'B' => 'WAKTU (WIB)',
            'C' => 'NAMA SISWA',
            'D' => 'MODUL',
            'E' => 'PERTANYAAN SISWA',
            'F' => 'JAWABAN CHATBOT AI',
            'G' => 'TIPE',
        ];

        foreach ($sheet2Headers as $col => $title) {
            $sheet2->setCellValue("{$col}3", $title);
        }

        $sheet2->getStyle('A3:G3')->getFont()->setBold(true);
        $sheet2->getStyle('A3:G3')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle('A3:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle('F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet2->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($greenHeaderBg);
        $sheet2->getStyle('A3:G3')->applyFromArray($thinBorder);

        // Ambil data seluruh chat histories
        $allChats = ChatHistory::with(['siswa', 'referensiChunk.module'])
            ->orderBy('created_at', 'desc')
            ->get();

        $cRow = 4;
        $cNo = 1;

        foreach ($allChats as $chat) {
            $namaSiswa = $chat->siswa ? $chat->siswa->name : 'Siswa';
            $waktu = $chat->created_at ? $chat->created_at->translatedFormat('d/m/Y H:i') : '-';
            
            $modulName = '-';
            if ($chat->referensiChunk && $chat->referensiChunk->module) {
                $modulName = $chat->referensiChunk->module->kb_nomor ?: $chat->referensiChunk->module->judul;
            }

            $isQuiz = ($chat->pertanyaan === '[LATIHAN_SOAL]');
            $pertanyaanText = $isQuiz ? 'Permintaan Latihan Soal AI' : $chat->pertanyaan;
            $tipeText = $isQuiz ? 'Latihan Soal' : 'Tanya Materi';

            $sheet2->setCellValue("A{$cRow}", $cNo);
            $sheet2->setCellValue("B{$cRow}", $waktu);
            $sheet2->setCellValue("C{$cRow}", $namaSiswa);
            $sheet2->setCellValue("D{$cRow}", $modulName);
            $sheet2->setCellValue("E{$cRow}", $pertanyaanText);
            $sheet2->setCellValue("F{$cRow}", $chat->jawaban);
            $sheet2->setCellValue("G{$cRow}", $tipeText);

            $sheet2->getStyle("A{$cRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("B{$cRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("D{$cRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("G{$cRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Wrap text untuk pertanyaan dan jawaban
            $sheet2->getStyle("E{$cRow}")->getAlignment()->setWrapText(true);
            $sheet2->getStyle("F{$cRow}")->getAlignment()->setWrapText(true);

            if ($cRow % 2 == 1) {
                $sheet2->getStyle("A{$cRow}:G{$cRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFAFA');
            }

            $sheet2->getStyle("A{$cRow}:G{$cRow}")->applyFromArray($thinBorder);

            $cRow++;
            $cNo++;
        }

        // Column widths for Sheet 2
        $sheet2->getColumnDimension('A')->setWidth(8);
        $sheet2->getColumnDimension('B')->setWidth(20);
        $sheet2->getColumnDimension('C')->setWidth(28);
        $sheet2->getColumnDimension('D')->setWidth(16);
        $sheet2->getColumnDimension('E')->setWidth(40);
        $sheet2->getColumnDimension('F')->setWidth(65);
        $sheet2->getColumnDimension('G')->setWidth(18);

        // Set active sheet back to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        // Stream File Download Response
        $filename = 'REKAP_AKTIVITAS_SISWA_SMKN1_KINALI_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ]);
    }
}
