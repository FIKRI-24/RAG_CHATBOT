<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->json('questions');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('module_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_quiz_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module_title');
            $table->string('quiz_title');
            $table->unsignedInteger('quiz_version');
            $table->json('answers');
            $table->json('questions_snapshot');
            $table->unsignedInteger('correct_count');
            $table->unsignedInteger('question_count');
            $table->unsignedTinyInteger('score');
            $table->timestamps();
            $table->index(['module_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_quiz_attempts');
        Schema::dropIfExists('module_quizzes');
    }
};
