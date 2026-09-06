<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nim',
        'prodi',
        'fakultas',
        'institusi',
        'produk',
        'email',
        'foto',
        'deskripsi',
    ];

    /**
     * Get the URL for the developer photo.
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    /**
     * Helper to get or create the single developer profile record.
     */
    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'nama' => 'Rudi Putra',
            'nim' => '25040030002',
            'prodi' => 'Pendidikan Guru Vokasi',
            'fakultas' => 'Pasca Sarjana',
            'institusi' => 'Universitas PGRI Sumatera Barat',
            'produk' => 'E-Modul terintegrasi Chatbot berbasis Web',
            'email' => 'putrarudi238@gmail.com',
            'foto' => null,
            'deskripsi' => 'Peneliti & Pengembang Media Pembelajaran Vokasi Teknik Komputer dan Jaringan',
        ]);
    }
}
