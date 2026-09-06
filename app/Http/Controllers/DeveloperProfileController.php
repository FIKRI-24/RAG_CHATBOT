<?php

namespace App\Http\Controllers;

use App\Models\DeveloperProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DeveloperProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengembang.
     */
    public function index(): View
    {
        $developer = DeveloperProfile::instance();

        return view('pengembang.index', compact('developer'));
    }

    /**
     * Khusus Guru: Unggah / Perbarui foto profil pengembang.
     */
    public function updateFoto(Request $request): RedirectResponse
    {
        if (!auth()->user() || !auth()->user()->isGuru()) {
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

        // Hapus foto lama jika ada
        if ($developer->foto && Storage::disk('public')->exists($developer->foto)) {
            Storage::disk('public')->delete($developer->foto);
        }

        $path = $request->file('foto')->store('pengembang', 'public');
        $developer->foto = $path;
        $developer->save();

        return redirect()->route('pengembang')->with('success', 'Foto profil pengembang berhasil diperbarui!');
    }

    /**
     * Khusus Guru: Hapus foto profil pengembang (kembali ke inisial).
     */
    public function destroyFoto(): RedirectResponse
    {
        if (!auth()->user() || !auth()->user()->isGuru()) {
            abort(403, 'Akses ditolak. Hanya guru yang dapat menghapus foto pengembang.');
        }

        $developer = DeveloperProfile::instance();

        if ($developer->foto && Storage::disk('public')->exists($developer->foto)) {
            Storage::disk('public')->delete($developer->foto);
        }

        $developer->foto = null;
        $developer->save();

        return redirect()->route('pengembang')->with('success', 'Foto profil pengembang berhasil dihapus dan dikembalikan ke avatar default.');
    }
}
