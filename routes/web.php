<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DeveloperProfileController;
use App\Http\Controllers\Guru\DashboardController;
use App\Http\Controllers\Guru\ModuleController;
use App\Http\Controllers\Guru\SiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect /dashboard ke dashboard sesuai role
Route::get('/dashboard', function () {
    if (auth()->user()->isGuru()) {
        return redirect()->route('guru.dashboard');
    }
    return redirect()->route('siswa.dashboard');
})->middleware(['auth'])->name('dashboard');

// Routes khusus Guru
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/petunjuk', function () {
        return view('guru.petunjuk');
    })->name('petunjuk');

    Route::post('/modules/{module}/reindex', [ModuleController::class, 'reindex'])->name('modules.reindex');
    Route::resource('modules', ModuleController::class);
    Route::get('/siswa/export-activity', [SiswaController::class, 'exportActivity'])->name('siswa.export');
    Route::resource('siswa', SiswaController::class)->except(['create', 'edit', 'show']);
});

// Route Download Materi (Bisa diakses Guru & Siswa terautentikasi)
Route::middleware(['auth'])->get('/modules/{module}/download', [ModuleController::class, 'download'])->name('modules.download');

use App\Http\Controllers\Siswa\ChatController;
use App\Http\Controllers\Siswa\ModuleController as SiswaModuleController;
use App\Http\Controllers\Siswa\PetunjukController;

// Routes khusus Siswa
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::post('/chat/ask', [ChatController::class, 'ask'])->middleware('throttle:60,1')->name('chat.ask');
    
    // E-Modul & Kegiatan Belajar (KB 1, 2, 3)
    Route::get('/modules', [SiswaModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{module}', [SiswaModuleController::class, 'show'])->name('modules.show');

    // Menu Petunjuk Siswa
    Route::get('/petunjuk', [PetunjukController::class, 'index'])->name('petunjuk');
});

// Profile routes (semua role)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Halaman Profil Pengembang (Guru & Siswa)
Route::middleware('auth')->group(function () {
    Route::get('/pengembang', [DeveloperProfileController::class, 'index'])->name('pengembang');
    
    // Khusus Guru: Upload & Hapus Foto Pengembang
    Route::middleware('role:guru')->group(function () {
        Route::post('/pengembang/foto', [DeveloperProfileController::class, 'updateFoto'])->name('pengembang.foto.update');
        Route::delete('/pengembang/foto', [DeveloperProfileController::class, 'destroyFoto'])->name('pengembang.foto.destroy');
    });
});

require __DIR__.'/auth.php';
