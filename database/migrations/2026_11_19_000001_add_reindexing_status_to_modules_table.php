<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('modules', 'reindexing_status')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('reindexing_status', 30)->nullable()->after('status_indexing');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('modules', 'reindexing_status')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('reindexing_status');
            });
        }
    }
};
