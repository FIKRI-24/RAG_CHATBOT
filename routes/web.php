<?php

use App\Http\Controllers\DeveloperProfileController;
use App\Http\Controllers\Guru\DashboardController;
use App\Http\Controllers\Guru\ModuleController;
use App\Http\Controllers\Guru\ModuleQuizController;
use App\Http\Controllers\Guru\QuizReviewController;
use App\Http\Controllers\Guru\SiswaController;
use App\Http\Controllers\Guru\SystemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\ChatController;
use App\Http\Controllers\Siswa\ModuleController as SiswaModuleController;
use App\Http\Controllers\Siswa\ModuleQuizController as SiswaModuleQuizController;
use App\Http\Controllers\Siswa\PetunjukController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/ping-version', function () {
    $error = null;
    try {
        \App\Models\Module::ensureKb4Module();
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }

    return response()->json([
        'status' => 'ok',
        'build' => 'commit-check-kb4',
        'error' => $error,
        'modules' => \App\Models\Module::all(['id', 'guru_id', 'kb_nomor', 'judul', 'status_indexing']),
        'teachers' => \App\Models\User::where('role', 'guru')->get(['id', 'name', 'email']),
    ]);
});

// Redirect /dashboard ke dashboard sesuai role
Route::get('/dashboard', function () {
    if (auth()->user()->isGuru()) {
        return redirect()->route('guru.dashboard');
    }

    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'active', 'auth.session', 'school.verified'])->name('dashboard');

// Routes khusus Guru
Route::middleware(['auth', 'active', 'auth.session', 'school.verified', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/petunjuk', function () {
        return view('guru.petunjuk');
    })->name('petunjuk');

    Route::post('/modules/{module}/reindex', [ModuleController::class, 'reindex'])->name('modules.reindex');
    Route::get('/modules/{module}/quiz', [ModuleQuizController::class, 'edit'])->name('modules.quiz.edit');
    Route::put('/modules/{module}/quiz', [ModuleQuizController::class, 'update'])->name('modules.quiz.update');
    Route::delete('/modules/{module}/quiz', [ModuleQuizController::class, 'destroy'])->name('modules.quiz.destroy');
    Route::get('/modules/{module}/quiz/results', [ModuleQuizController::class, 'results'])->name('modules.quiz.results');
    Route::resource('modules', ModuleController::class);
    Route::get('/siswa/export-activity', [SiswaController::class, 'exportActivity'])->name('siswa.export');
    Route::patch('/siswa/{siswa}/status', [SiswaController::class, 'status'])->name('siswa.status');
    Route::get('/quiz-reviews', [QuizReviewController::class, 'index'])->name('quiz-reviews.index');
    Route::patch('/quiz-reviews/{chat}', [QuizReviewController::class, 'update'])->name('quiz-reviews.update');
    Route::get('/system', [SystemController::class, 'index'])->name('system');
    Route::resource('siswa', SiswaController::class)->except(['create', 'edit', 'show']);
});

// Route Download Materi (Bisa diakses Guru & Siswa terautentikasi)
Route::middleware(['auth', 'active', 'auth.session', 'school.verified'])->get('/modules/{module}/download', [ModuleController::class, 'download'])->name('modules.download');


// Routes khusus Siswa
Route::middleware(['auth', 'active', 'auth.session', 'school.verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::post('/chat/ask', [ChatController::class, 'ask'])->middleware('throttle:60,1')->name('chat.ask');

    // E-Modul & Kegiatan Belajar (KB 1, 2, 3)
    Route::get('/modules', [SiswaModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{module}', [SiswaModuleController::class, 'show'])->name('modules.show');
    Route::get('/modules/{module}/quiz', [SiswaModuleQuizController::class, 'show'])->name('modules.quiz.show');
    Route::post('/modules/{module}/quiz', [SiswaModuleQuizController::class, 'submit'])->middleware('throttle:20,1')->name('modules.quiz.submit');
    Route::get('/quiz-attempts/{attempt}', [SiswaModuleQuizController::class, 'result'])->name('quiz-attempts.show');

    // Menu Petunjuk Siswa
    Route::get('/petunjuk', [PetunjukController::class, 'index'])->name('petunjuk');
});

// Profile routes (semua role)
Route::middleware(['auth', 'active', 'auth.session'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Halaman Profil Pengembang (Khusus Role Guru)
Route::middleware(['auth', 'active', 'auth.session', 'school.verified', 'role:guru'])->group(function () {
    Route::get('/pengembang', [DeveloperProfileController::class, 'index'])->name('pengembang');
    Route::post('/pengembang/foto', [DeveloperProfileController::class, 'updateFoto'])->name('pengembang.foto.update');
    Route::delete('/pengembang/foto', [DeveloperProfileController::class, 'destroyFoto'])->name('pengembang.foto.destroy');
});

require __DIR__.'/auth.php';
