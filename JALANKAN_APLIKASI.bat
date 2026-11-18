@echo off
title E-Modul TKJ RAG Chatbot - SMKN 1 Kinali
color 0A

setlocal enabledelayedexpansion
cd /d "%~dp0"

:: Cek executable PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    ) else if exist "C:\xampp_new\php\php.exe" (
        set "PATH=C:\xampp_new\php;%PATH%"
    ) else (
        echo [ERROR] Program PHP tidak ditemukan di sistem atau di C:\xampp\php\
        echo Pastikan XAMPP terinstal dan folder C:\xampp\php tersedia.
        pause
        exit /b 1
    )
)

echo ================================================================
echo       SISTEM MEDIA PEMBELAJARAN RAG CHATBOT AI (TKJ)
echo                   SMK NEGERI 1 KINALI
echo ================================================================
echo.

:: Siapkan file .env otomatis jika belum ada (instalasi pertama di laptop client)
if not exist ".env" (
    if exist ".env.example" (
        echo [SETUP] Membuat file .env dari .env.example...
        copy /Y ".env.example" ".env" >nul
        php artisan key:generate --force
        echo.
        echo [PERHATIAN] File .env baru saja dibuat.
        echo Silakan isi GEMINI_API_KEY di file .env agar fitur Asisten AI aktif.
        echo.
    ) else (
        echo [ERROR] File .env dan .env.example tidak ditemukan.
        pause
        exit /b 1
    )
)

:: Pastikan storage link tersedia di public\storage
if not exist "public\storage" (
    echo [SETUP] Membuat tautan penyimpanan public\storage...
    php artisan storage:link
)

echo [1/4] Memastikan struktur database terbaru (migrate)...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Gagal terhubung atau menjalankan migrasi database!
    echo Pastikan Apache & MySQL di XAMPP sudah Start, dan database 'rag_chatbot' sudah dibuat.
    pause
    exit /b 1
)

echo [2/4] Membersihkan cache konfigurasi & view lama...
php artisan config:clear >nul 2>&1
php artisan view:clear >nul 2>&1

echo [3/4] Menyiapkan antrean pemrosesan RAG (Queue Worker)...
start "Worker Antrean AI (RAG)" /D "%~dp0" cmd /k "php artisan queue:work rag --queue=rag --sleep=3 --tries=3"

echo [4/4] Menjalankan server aplikasi (Lokal ^& Jaringan LAN Kelas)...
echo ----------------------------------------------------------------
echo  * Akses Lokal / Demo         : http://127.0.0.1:8000
echo  * Akses Kelas 30 Siswa (Apache XAMPP Multi-Thread, Disarankan):
echo    http://localhost/RAG_CHATBOT/public  atau  http://[IP-LAN-SERVER]/RAG_CHATBOT/public
echo ----------------------------------------------------------------
echo (CATATAN: Jangan tutup jendela ini selama aplikasi sedang digunakan)
echo.
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000
php artisan serve --host=0.0.0.0 --port=8000
pause
