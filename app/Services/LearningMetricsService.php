<?php

namespace App\Services;

class LearningMetricsService
{
    public function summary(): array
    {
        $access = app(StudentAccessService::class);
        $students = $access->students()->count();
        $active = $access->students()->whereHas('chatHistories')->count();
        $questions = $access->chats()->questions()->count();

        return [
            'students' => $students, 'active_students' => $active,
            'questions' => $questions, 'quizzes' => $access->chats()->quizzes()->count(),
            'interactions' => $access->chats()->count(),
            'active_percent' => $students ? round($active / $students * 100, 1) : 0,
            'questions_per_student' => $students ? round($questions / $students, 2) : 0,
        ];
    }
}
