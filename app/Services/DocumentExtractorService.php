<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class DocumentExtractorService
{
    /**
     * Extract text from a document based on its file type.
     *
     * @throws Exception
     */
    public function extract(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (filesize($filePath) > 10 * 1024 * 1024) {
            throw new Exception('Dokumen melebihi batas 10 MB.');
        }
        if ($extension === 'docx') {
            $zip = new \ZipArchive;
            if ($zip->open($filePath, \ZipArchive::RDONLY) !== true) {
                throw new Exception('Arsip DOCX tidak valid.');
            }
            try {
                $size = 0;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->statIndex($i);
                    $size += $entry['size'];
                    if ($size > 50 * 1024 * 1024 || $zip->numFiles > 5000) {
                        throw new Exception('DOCX terlalu besar setelah dibuka. Pisahkan dokumen.');
                    }
                }
            } finally {
                $zip->close();
            }
        }

        try {
            if ($extension === 'pdf') {
                return $this->extractFromPdf($filePath);
            } elseif (in_array($extension, ['doc', 'docx'])) {
                return $this->extractFromWord($filePath);
            } else {
                throw new InvalidArgumentException("Format file tidak didukung: {$extension}");
            }
        } catch (Exception $e) {
            throw new Exception('Gagal mengekstrak dokumen: '.$e->getMessage());
        }
    }

    /**
     * Extract text from a PDF file.
     *
     * @throws Exception
     */
    protected function extractFromPdf(string $filePath): string
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }

    /**
     * Extract text from a Word document (.docx).
     *
     * @throws Exception
     */
    protected function extractFromWord(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractTextFromElement($element)."\n\n";
            }
        }

        return trim($text);
    }

    /**
     * Recursively extract text from PhpWord element.
     *
     * @param  mixed  $element
     */
    protected function extractTextFromElement($element): string
    {
        $text = '';

        if ($element instanceof Text) {
            $text .= $element->getText().' ';
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractTextFromElement($child);
            }
        } elseif ($element instanceof Table) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $child) {
                        $text .= $this->extractTextFromElement($child);
                    }
                    $text .= ' | ';
                }
                $text .= "\n";
            }
        } elseif (method_exists($element, 'getText')) {
            $val = $element->getText();
            if (is_string($val)) {
                $text .= $val.' ';
            }
        }

        return $text;
    }
}
