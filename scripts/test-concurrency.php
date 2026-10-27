<?php

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

use App\Models\Module;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== MEMULAI PENGUJIAN KONKURENSI DUA PROSES PARALEL PADA MYSQL ===\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";

// 1. Setup Fixture Users & Module
$guru = User::firstOrCreate(['email' => 'guru_concurrency@example.test'], [
    'name' => 'Guru Concurrency',
    'password' => bcrypt('password'),
    'role' => 'guru',
]);

$siswaQuota = User::firstOrCreate(['email' => 'siswa_quota@example.test'], [
    'name' => 'Siswa Quota',
    'password' => bcrypt('password'),
    'role' => 'siswa',
]);

$siswaQuiz = User::firstOrCreate(['email' => 'siswa_quiz@example.test'], [
    'name' => 'Siswa Quiz',
    'password' => bcrypt('password'),
    'role' => 'siswa',
]);

$module = Module::firstOrCreate(['judul' => 'Modul Konkurensi'], [
    'guru_id' => $guru->id,
    'mapel' => 'Teknik Komputer dan Jaringan',
    'kb_nomor' => 'KB 1',
    'file_path' => 'modules/test_concurrency.pdf',
    'status_indexing' => 'completed',
]);

// ---------------------------------------------------------
// TEST 1: Konkurensi Kuota AI (Atomic Conditional UPDATE)
// ---------------------------------------------------------
echo "\n--- [TEST 1] Uji Konkurensi Kuota AI (Batas: 4 request, 2 worker x 5 request = 10 total) ---\n";
DB::table('ai_usage')->truncate();

$p1 = new Process(['php', 'scripts/concurrency-worker.php', 'quota', '1', (string) $siswaQuota->id], base_path(), ['APP_ENV' => 'testing']);
$p2 = new Process(['php', 'scripts/concurrency-worker.php', 'quota', '2', (string) $siswaQuota->id], base_path(), ['APP_ENV' => 'testing']);

// Jalankan kedua proses secara paralel (non-blocking start)
$p1->start();
$p2->start();

// Tunggu kedua proses selesai
$p1->wait();
$p2->wait();

$out1 = json_decode($p1->getOutput(), true);
$out2 = json_decode($p2->getOutput(), true);

$totalSuccess = ($out1['success'] ?? 0) + ($out2['success'] ?? 0);
$totalRejected = ($out1['rejected'] ?? 0) + ($out2['rejected'] ?? 0);

$dbUserRequests = (int) DB::table('ai_usage')->where('bucket', 'user:'.$siswaQuota->id)->value('requests');
$dbGlobalRequests = (int) DB::table('ai_usage')->where('bucket', 'global')->value('requests');

echo "Worker 1 Output: " . $p1->getOutput() . "\n";
echo "Worker 2 Output: " . $p2->getOutput() . "\n";
echo "Total Request Diterima: {$totalSuccess} (Ekspektasi: 4)\n";
echo "Total Request Ditolak : {$totalRejected} (Ekspektasi: 6)\n";
echo "Data ai_usage di DB   : user:{$siswaQuota->id} = {$dbUserRequests}, global = {$dbGlobalRequests}\n";

$quotaOk = ($totalSuccess === 4 && $totalRejected === 6 && $dbUserRequests === 4 && $dbGlobalRequests === 4);
echo "Status Test Kuota AI  : " . ($quotaOk ? "LULUS (Tidak ada over-quota, rollback global sukses, tanpa deadlock)\n" : "GAGAL\n");

// ---------------------------------------------------------
// TEST 2: Konkurensi Submit Kuis (Unique Index module_id, user_id)
// ---------------------------------------------------------
echo "\n--- [TEST 2] Uji Konkurensi Submit Kuis (2 worker mengirim kuis yang sama secara simultan) ---\n";
DB::table('module_quiz_attempts')->where('module_id', $module->id)->where('user_id', $siswaQuiz->id)->delete();

$q1 = new Process(['php', 'scripts/concurrency-worker.php', 'quiz', '1', (string) $siswaQuiz->id, (string) $module->id], base_path(), ['APP_ENV' => 'testing']);
$q2 = new Process(['php', 'scripts/concurrency-worker.php', 'quiz', '2', (string) $siswaQuiz->id, (string) $module->id], base_path(), ['APP_ENV' => 'testing']);

// Jalankan kedua proses secara paralel (simultan)
$q1->start();
$q2->start();

// Tunggu keduanya selesai
$q1->wait();
$q2->wait();

$res1 = json_decode($q1->getOutput(), true);
$res2 = json_decode($q2->getOutput(), true);

$attemptsInDb = DB::table('module_quiz_attempts')->where('module_id', $module->id)->where('user_id', $siswaQuiz->id)->count();

echo "Worker 1 Output: " . $q1->getOutput() . "\n";
echo "Worker 2 Output: " . $q2->getOutput() . "\n";
echo "Jumlah baris di database untuk (module 101, user 888): {$attemptsInDb} (Ekspektasi: 1)\n";

$statuses = [($res1['status'] ?? ''), ($res2['status'] ?? '')];
sort($statuses);

$quizOk = ($attemptsInDb === 1 && $statuses === ['inserted', 'rejected_duplicate']);
echo "Status Test Submit Kuis: " . ($quizOk ? "LULUS (Tepat 1 baris masuk, 1 baris ditolak unique constraint, tanpa duplikat/deadlock)\n" : "GAGAL\n");

if ($quotaOk && $quizOk) {
    echo "\nKESIMPULAN: SELURUH UJI KONKURENSI MYSQL 100% LULUS TANPA DEADLOCK, TANPA OVER-QUOTA, DAN TANPA DUPLIKASI DATA!\n";
    exit(0);
} else {
    echo "\nKESIMPULAN: ADA UJI KONKURENSI YANG GAGAL.\n";
    exit(1);
}
