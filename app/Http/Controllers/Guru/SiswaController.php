<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use App\Services\AuditService;
use App\Services\ExportActivityService;
use App\Services\StudentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Tampilkan daftar akun siswa.
     */
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255']);

        $query = User::where('role', 'siswa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $siswas = $query->latest()->paginate(10)->withQueryString();
        $totalSiswa = User::where('role', 'siswa')->count();

        return view('guru.siswa.index', compact('siswas', 'totalSiswa'));
    }

    /**
     * Tambah akun siswa baru.
     */
    public function store(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'bail|required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:1024',
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'email.required' => 'Email / Username siswa wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar untuk siswa lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
        ]);

        $request->validate(['class_name' => 'nullable|string|max:100', 'student_number' => 'nullable|string|max:100']);
        DB::transaction(function () use ($request) {
            $student = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'siswa',
                'class_name' => $request->class_name,
                'student_number' => $request->student_number,
            ]);
            app(AuditService::class)->record('account.created', $student, ['role' => 'siswa']);
        });

        return redirect()->route('guru.siswa.index')->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    /**
     * Perbarui data & kredensial login siswa.
     */
    public function update(Request $request, User $siswa)
    {
        app(StudentAccessService::class)->authorize($siswa);
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        if (! $siswa->isSiswa()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['bail', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($siswa->id)],
            'password' => 'nullable|string|min:8|max:1024',
            'class_name' => 'nullable|string|max:100',
            'student_number' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'email.required' => 'Email / Username wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        DB::transaction(function () use ($request, $siswa) {
            $siswa = User::whereKey($siswa->id)->lockForUpdate()->firstOrFail();
            $siswa->name = $request->name;
            $siswa->email = $request->email;
            $siswa->fill($request->only('class_name', 'student_number'));

            if ($request->filled('password')) {
                $siswa->password = Hash::make($request->password);
            }

            $siswa->save();
            app(AuditService::class)->record('account.updated', $siswa, ['password_changed' => $request->filled('password')]);
        });
        if ($request->filled('password')) {
            app(AccountService::class)->revokeSessions($siswa);
        }

        return redirect()->route('guru.siswa.index')->with('success', 'Data & password siswa berhasil diperbarui.');
    }

    /**
     * Hapus akun siswa.
     */
    public function destroy(User $siswa)
    {
        if (! $siswa->isSiswa()) {
            abort(403, 'Akses ditolak.');
        }

        app(StudentAccessService::class)->authorize($siswa);
        app(AccountService::class)->delete($siswa);

        return redirect()->route('guru.siswa.index')->with('success', 'Akun siswa berhasil dihapus.');
    }

    public function status(Request $request, User $siswa)
    {
        app(StudentAccessService::class)->authorize($siswa);
        $request->validate(['is_active' => 'required|boolean']);
        app(AccountService::class)->setActive($siswa, $request->boolean('is_active'));

        return back()->with('success', $request->boolean('is_active') ? 'Akun siswa diaktifkan.' : 'Akun dinonaktifkan. Riwayat belajar tetap tersimpan.');
    }

    /**
     * Ekspor data rekapitulasi aktivitas siswa ke Excel (.xlsx) dengan Kop Resmi & Times New Roman 12pt.
     */
    public function exportActivity(ExportActivityService $exportService)
    {
        return $exportService->export();
    }
}
