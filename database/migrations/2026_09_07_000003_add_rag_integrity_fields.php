<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->uuid('indexing_version')->nullable();
            $table->text('indexing_error')->nullable();
        });
        Schema::table('module_chunks', function (Blueprint $table) {
            $table->string('embedding_model')->nullable();
            $table->unsignedInteger('embedding_dimensions')->nullable();
            $table->unsignedInteger('chunk_index')->nullable();
        });
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->string('mapel')->nullable();
            $table->string('kind')->default('answer');
            $table->string('quiz_status')->nullable();
            $table->json('sources')->nullable();
            $table->text('retrieval_query')->nullable();
            $table->index(['siswa_id', 'kind', 'mapel']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->dropIndex(['siswa_id', 'kind', 'mapel']);
            $table->dropColumn(['mapel', 'kind', 'quiz_status', 'sources', 'retrieval_query']);
        });
        Schema::table('module_chunks', fn (Blueprint $table) => $table->dropColumn(['embedding_model', 'embedding_dimensions', 'chunk_index']));
        Schema::table('modules', fn (Blueprint $table) => $table->dropColumn(['indexing_version', 'indexing_error']));
    }
};
