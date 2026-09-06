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
        Schema::create('developer_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->default('Rudi Putra');
            $table->string('nim')->default('25040030002');
            $table->string('prodi')->default('Pendidikan Guru Vokasi');
            $table->string('fakultas')->default('Pasca Sarjana');
            $table->string('institusi')->default('Universitas PGRI Sumatera Barat');
            $table->string('produk')->default('E-Modul terintegrasi Chatbot berbasis Web');
            $table->string('email')->default('putrarudi238@gmail.com');
            $table->string('foto')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developer_profiles');
    }
};
