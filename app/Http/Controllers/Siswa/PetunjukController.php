<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PetunjukController extends Controller
{
    /**
     * Tampilkan panduan penggunaan E-Modul dan Chatbot AI untuk Siswa.
     */
    public function index()
    {
        return view('siswa.petunjuk');
    }
}
