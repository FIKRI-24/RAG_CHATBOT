<?php

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

use App\Exceptions\RagException;
use App\Models\User;
use App\Services\AiUsageService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$action = $argv[1] ?? '';
$workerId = $argv[2] ?? 1;

if ($action === 'quota') {
    $userId = (int) ($argv[3] ?? 999);
    $user = User::find($userId);
    if (! $user) {
        $user = new User;
        $user->id = $userId;
    }
    Auth::setUser($user);

    config(['rag.daily_user_limit' => 4, 'rag.daily_global_limit' => 20]);
    $service = app(AiUsageService::class);
    $success = 0;
    $rejected = 0;

    for ($i = 0; $i < 5; $i++) {
        try {
            $service->reserve();
            $success++;
        } catch (RagException $e) {
            $rejected++;
        } catch (\Throwable $e) {
            echo json_encode(['worker' => $workerId, 'error' => $e->getMessage()]);
            exit(1);
        }
        usleep(5000); // 5ms
    }

    echo json_encode(['worker' => $workerId, 'success' => $success, 'rejected' => $rejected]);
    exit(0);
}

if ($action === 'quiz') {
    $userId = (int) ($argv[3] ?? 888);
    $moduleId = (int) ($argv[4] ?? 101);

    try {
        DB::table('module_quiz_attempts')->insert([
            'module_id' => $moduleId,
            'user_id' => $userId,
            'module_title' => 'Concurrent Module',
            'quiz_title' => 'Concurrent Quiz',
            'quiz_version' => 1,
            'answers' => json_encode(['A']),
            'questions_snapshot' => json_encode([]),
            'correct_count' => 1,
            'question_count' => 1,
            'score' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo json_encode(['worker' => $workerId, 'status' => 'inserted']);
    } catch (\Illuminate\Database\QueryException $e) {
        echo json_encode(['worker' => $workerId, 'status' => 'rejected_duplicate', 'sqlstate' => $e->getCode()]);
    } catch (\Throwable $e) {
        echo json_encode(['worker' => $workerId, 'status' => 'error', 'message' => $e->getMessage()]);
    }
    exit(0);
}
