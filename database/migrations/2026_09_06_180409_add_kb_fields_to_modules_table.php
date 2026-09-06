<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('kb_nomor')->default('KB 1')->after('mapel');
            $table->text('tp')->nullable()->after('kb_nomor');
            $table->string('video_url')->nullable()->after('file_path');
            $table->string('kuis_url')->nullable()->after('video_url');

            $table->index('kb_nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex(['kb_nomor']);
            $table->dropColumn(['kb_nomor', 'tp', 'video_url', 'kuis_url']);
        });
    }
};
