<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Tampilkan daftar akun siswa.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'siswa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $siswas = $query->latest()->paginate(10);
        $totalSiswa = User::where('role', 'siswa')->count();

        return view('guru.siswa.index', compact('siswas', 'totalSiswa'));
    }

    /**
     * Tambah akun siswa baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'email.required' => 'Email / Username siswa wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar untuk siswa lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
        ]);

        return redirect()->route('guru.siswa.index')->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    /**
     * Perbarui data & kredensial login siswa.
     */
    public function update(Request $request, User $siswa)
    {
        if (!$siswa->isSiswa()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($siswa->id)],
            'password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'email.required' => 'Email / Username wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $siswa->name = $request->name;
        $siswa->email = $request->email;

        if ($request->filled('password')) {
            $siswa->password = Hash::make($request->password);
        }

        $siswa->save();

        return redirect()->route('guru.siswa.index')->with('success', 'Data & password siswa berhasil diperbarui.');
    }

    /**
     * Hapus akun siswa.
     */
    public function destroy(User $siswa)
    {
        if (!$siswa->isSiswa()) {
            abort(403, 'Akses ditolak.');
        }

        $siswa->delete();

        return redirect()->route('guru.siswa.index')->with('success', 'Akun siswa berhasil dihapus.');
    }
}
