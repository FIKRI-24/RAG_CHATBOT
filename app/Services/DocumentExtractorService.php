<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class DocumentExtractorService
{
    /**
     * Extract text from a document based on its file type.
     *
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    public function extract(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            if ($extension === 'pdf') {
                return $this->extractFromPdf($filePath);
            } elseif (in_array($extension, ['doc', 'docx'])) {
                return $this->extractFromWord($filePath);
            } else {
                throw new InvalidArgumentException("Format file tidak didukung: {$extension}");
            }
        } catch (Exception $e) {
            throw new Exception("Gagal mengekstrak dokumen: " . $e->getMessage());
        }
    }

    /**
     * Extract text from a PDF file.
     *
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    protected function extractFromPdf(string $filePath): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        return $pdf->getText();
    }

    /**
     * Extract text from a Word document (.docx).
     *
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    protected function extractFromWord(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractTextFromElement($element) . "\n\n";
            }
        }

        return trim($text);
    }

    /**
     * Recursively extract text from PhpWord element.
     *
     * @param mixed $element
     * @return string
     */
    protected function extractTextFromElement($element): string
    {
        $text = '';

        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            $text .= $element->getText() . ' ';
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractTextFromElement($child);
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
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
                $text .= $val . ' ';
            }
        }

        return $text;
    }
}
