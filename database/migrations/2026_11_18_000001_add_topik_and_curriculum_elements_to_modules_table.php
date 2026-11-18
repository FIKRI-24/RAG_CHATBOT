<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('modules', 'topik')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('topik')->nullable()->after('mapel');
            });
        }

        DB::table('modules')
            ->where('mapel', 'Teknik Komputer dan Jaringan')
            ->whereIn('kb_nomor', ['KB 1', 'KB 2', 'KB 3'])
            ->update([
                'mapel' => 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
                'topik' => DB::raw("COALESCE(NULLIF(topik, ''), 'Instalasi Jaringan Nirkabel')"),
            ]);

        DB::table('modules')
            ->where('mapel', 'Teknik Komputer dan Jaringan')
            ->where('kb_nomor', 'KB 4')
            ->update([
                'mapel' => 'Elemen 3: Keamanan Jaringan',
                'topik' => DB::raw("COALESCE(NULLIF(topik, ''), 'Keamanan Jaringan dan Firewall')"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('modules')
            ->where('mapel', 'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel')
            ->where('topik', 'Instalasi Jaringan Nirkabel')
            ->whereIn('kb_nomor', ['KB 1', 'KB 2', 'KB 3'])
            ->update([
                'mapel' => 'Teknik Komputer dan Jaringan',
            ]);

        DB::table('modules')
            ->where('mapel', 'Elemen 3: Keamanan Jaringan')
            ->where('topik', 'Keamanan Jaringan dan Firewall')
            ->where('kb_nomor', 'KB 4')
            ->update([
                'mapel' => 'Teknik Komputer dan Jaringan',
            ]);

        if (Schema::hasColumn('modules', 'topik')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('topik');
            });
        }
    }
};
