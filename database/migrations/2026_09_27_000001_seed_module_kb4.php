<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: Initial module data seeding is handled via DatabaseSeeder / ModuleKb4Seeder,
     * not inside schema migrations (per PRD §7.6).
     */
    public function up(): void
    {
        // No-op: data seeding is managed by seeders.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: do not delete teacher-managed KB 4 modules on schema rollback.
    }
};
