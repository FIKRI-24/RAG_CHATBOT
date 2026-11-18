<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (! Schema::hasColumn('modules', 'indexed_file_path')) {
                $table->string('indexed_file_path')->nullable()->after('file_path');
            }
        });

        Schema::table('module_quiz_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('module_quiz_attempts', 'attempt_number')) {
                $table->unsignedInteger('attempt_number')->default(1)->after('score');
            }
            if (! Schema::hasColumn('module_quiz_attempts', 'previous_attempts')) {
                $table->json('previous_attempts')->nullable()->after('attempt_number');
            }
            if (! Schema::hasColumn('module_quiz_attempts', 'remedial_approved_at')) {
                $table->timestamp('remedial_approved_at')->nullable()->after('previous_attempts');
            }
            if (! Schema::hasColumn('module_quiz_attempts', 'remedial_approved_by')) {
                $table->foreignId('remedial_approved_by')->nullable()->after('remedial_approved_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('module_quiz_attempts', 'remedial_note')) {
                $table->string('remedial_note', 500)->nullable()->after('remedial_approved_by');
            }
            if (! Schema::hasColumn('module_quiz_attempts', 'adjusted_score_by')) {
                $table->foreignId('adjusted_score_by')->nullable()->after('remedial_note')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('module_quiz_attempts', 'adjusted_at')) {
                $table->timestamp('adjusted_at')->nullable()->after('adjusted_score_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('module_quiz_attempts', function (Blueprint $table) {
            foreach (['adjusted_score_by', 'remedial_approved_by'] as $fkColumn) {
                if (Schema::hasColumn('module_quiz_attempts', $fkColumn)) {
                    $table->dropConstrainedForeignId($fkColumn);
                }
            }
            foreach (['adjusted_at', 'remedial_note', 'remedial_approved_at', 'previous_attempts', 'attempt_number'] as $column) {
                if (Schema::hasColumn('module_quiz_attempts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'indexed_file_path')) {
                $table->dropColumn('indexed_file_path');
            }
        });
    }
};
