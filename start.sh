#!/bin/bash
set -e

echo "=== Memulai Inisialisasi Aplikasi TKJ RAG Chatbot ==="

# 1. Pastikan folder direktori storage dan izin akses siap
mkdir -p storage/app/private/modules
mkdir -p storage/app/public/pengembang
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 2. Salin materi default KB1-KB4 jika belum ada di folder storage (misal akibat persistent volume baru)
if [ -d "resources/default-modules" ]; then
    echo "Sinkronisasi berkas modul pembelajaran bawaan..."
    cp -n resources/default-modules/* storage/app/private/modules/ 2>/dev/null || true
fi

# 3. Jalankan migrasi database
if [ "$RAILPACK_SKIP_MIGRATIONS" != "true" ]; then
    echo "Menjalankan migrasi database..."
    php artisan migrate --force
fi

# 4. Link storage dan optimasi cache aplikasi
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize

# 5. Jalankan Worker Antrean AI (RAG Queue) di latar belakang
echo "Menjalankan Worker Antrean AI RAG (queue:work rag)..."
php artisan queue:work rag --queue=rag --sleep=3 --tries=3 --timeout=300 &

# 6. Jalankan Server Web FrankenPHP
echo "Menjalankan FrankenPHP Server..."
exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
