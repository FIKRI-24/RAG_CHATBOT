<?php

use Database\Seeders\ModuleKb4Seeder;
use Illuminate\Database\Migrations\Migration;
use App\Models\Module;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $seeder = new ModuleKb4Seeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $module = Module::where('kb_nomor', 'KB 4')->first();
        if ($module) {
            $module->quiz()?->delete();
            $module->delete();
        }
    }
};
