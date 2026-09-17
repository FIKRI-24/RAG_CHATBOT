<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('auth_version')->default(1);
            $table->string('class_name', 100)->nullable();
            $table->string('student_number', 100)->nullable();
            $table->string('teacher_number', 100)->nullable();
            $table->index('role');
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->json('additional_videos')->nullable();
        });
        Schema::table('chat_histories', function (Blueprint $table) {
            // Deliberately not a FK: history retains its original scope after source deletion.
            $table->unsignedBigInteger('scope_module_id')->nullable()->index();
            $table->foreignId('quiz_id')->nullable()->constrained('chat_histories')->nullOnDelete();
            $table->json('quiz_payload')->nullable();
            $table->json('assessment')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->index(['siswa_id', 'id']);
            $table->index('created_at');
        });
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('action', 100)->index();
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->index();
        });
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->string('bucket', 100);
            $table->date('day');
            $table->unsignedInteger('requests')->default(0);
            $table->unique(['bucket', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
        Schema::dropIfExists('audit_events');
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['scope_module_id']);
            $table->dropIndex(['siswa_id', 'id']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['scope_module_id', 'quiz_id', 'quiz_payload', 'assessment', 'score', 'reviewed_by', 'reviewed_at', 'review_note']);
        });
        Schema::table('modules', fn (Blueprint $table) => $table->dropColumn('additional_videos'));
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['is_active', 'auth_version', 'class_name', 'student_number', 'teacher_number']);
        });
    }
};
