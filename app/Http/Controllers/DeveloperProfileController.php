<?php

namespace App\Http\Controllers;

use App\Models\DeveloperProfile;
use App\Services\StoredFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DeveloperProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengembang.
     */
    public function index(): View
    {
        if (! auth()->user() || ! auth()->user()->isGuru()) {
            abort(403, 'Akses ditolak. Profil pengembang hanya dapat diakses oleh guru.');
        }

        $developer = DeveloperProfile::instance();

        return view('pengembang.index', compact('developer'));
    }

    /**
     * Khusus Guru: Unggah / Perbarui foto profil pengembang.
     */
    public function updateFoto(Request $request): RedirectResponse
    {
        if (! auth()->user() || ! auth()->user()->isGuru()) {
            abort(403, 'Akses ditolak. Hanya guru yang dapat mengubah foto pengembang.');
        }

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'foto.required' => 'Silakan pilih berkas foto terlebih dahulu.',
            'foto.image' => 'Berkas yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Format gambar yang didukung: JPG, JPEG, PNG, WEBP.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $developer = DeveloperProfile::instance();

        $files = app(StoredFileService::class);
        $path = $files->store($request->file('foto'), 'pengembang', 'public', 'foto');
        $oldPath = null;
        try {
            DB::transaction(function () use ($developer, $path, &$oldPath) {
                $current = DeveloperProfile::whereKey($developer->id)->lockForUpdate()->firstOrFail();
                $oldPath = $current->foto;
                $current->update(['foto' => $path]);
            });
        } catch (\Throwable $e) {
            $files->delete($path, 'public');
            Log::warning('Developer photo update failed', ['error_type' => get_class($e)]);

            return back()->withErrors(['foto' => 'Foto belum dapat diperbarui. Foto sebelumnya tetap tersedia.']);
        }
        $files->delete($oldPath, 'public');

        return redirect()->route('pengembang')->with('success', 'Foto profil pengembang berhasil diperbarui!');
    }

    /**
     * Khusus Guru: Hapus foto profil pengembang (kembali ke inisial).
     */
    public function destroyFoto(): RedirectResponse
    {
        if (! auth()->user() || ! auth()->user()->isGuru()) {
            abort(403, 'Akses ditolak. Hanya guru yang dapat menghapus foto pengembang.');
        }

        $developer = DeveloperProfile::instance();

        $path = DB::transaction(function () use ($developer) {
            $current = DeveloperProfile::whereKey($developer->id)->lockForUpdate()->firstOrFail();
            $path = $current->foto;
            $current->update(['foto' => null]);

            return $path;
        });
        app(StoredFileService::class)->delete($path, 'public');

        return redirect()->route('pengembang')->with('success', 'Foto profil pengembang berhasil dihapus dan dikembalikan ke avatar default.');
    }
}
