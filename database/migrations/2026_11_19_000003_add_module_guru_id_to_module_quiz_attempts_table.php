<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_quiz_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('module_quiz_attempts', 'module_guru_id')) {
                $table->unsignedBigInteger('module_guru_id')->nullable()->after('module_id')->index();
            }
        });

        // Backfill owner guru_id from existing modules so authorization persists even if a module is deleted later.
        $pairs = DB::table('modules')->pluck('guru_id', 'id');
        foreach ($pairs as $moduleId => $guruId) {
            DB::table('module_quiz_attempts')
                ->where('module_id', $moduleId)
                ->whereNull('module_guru_id')
                ->update(['module_guru_id' => $guruId]);
        }
    }

    public function down(): void
    {
        Schema::table('module_quiz_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('module_quiz_attempts', 'module_guru_id')) {
                $table->dropIndex(['module_guru_id']);
                $table->dropColumn('module_guru_id');
            }
        });
    }
};
