<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoredFileService
{
    public function store(UploadedFile $file, string $directory, string $disk, string $field): string
    {
        try {
            $path = $file->store($directory, $disk);
            if (is_string($path) && $path !== '') {
                return $path;
            }
        } catch (Throwable $e) {
            Log::warning('File upload failed', ['error_type' => get_class($e)]);
        }

        throw ValidationException::withMessages([$field => 'Berkas belum dapat disimpan. Periksa ruang dan izin penyimpanan server, lalu coba lagi.']);
    }

    public function delete(?string $path, string $disk): bool
    {
        if (! $path) {
            return true;
        }
        try {
            if (! Storage::disk($disk)->exists($path) || Storage::disk($disk)->delete($path)) {
                return true;
            }
        } catch (Throwable $e) {
            Log::warning('File cleanup failed', ['error_type' => get_class($e)]);
        }
        Log::warning('File needs cleanup', ['disk' => $disk, 'path' => $path]);

        return false;
    }
}
