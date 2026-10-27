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

# 3. Jalankan migrasi database jika tidak dinonaktifkan
if [ "$RAILPACK_SKIP_MIGRATIONS" != "true" ] && [ "$SKIP_STARTUP_MIGRATIONS" != "true" ]; then
    echo "Menjalankan migrasi database..."
    php artisan migrate --force
fi

# 4. Link storage dan optimasi cache aplikasi
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize

# 5. Jalankan Worker Antrean AI (RAG Queue) dengan loop autorestart jika diaktifkan
if [ "${RUN_EMBEDDED_WORKER:-true}" = "true" ]; then
    echo "Menjalankan Supervised Worker Antrean AI RAG (queue:work rag)..."
    (
        while true; do
            php artisan queue:work rag --queue=rag --sleep=3 --tries=3 --timeout=300 --max-time=3600 || true
            sleep 3
        done
    ) &
fi

# 6. Jalankan Server Web
if command -v docker-php-entrypoint >/dev/null 2>&1 && [ -f "/Caddyfile" ]; then
    echo "Menjalankan FrankenPHP Server..."
    exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
else
    echo "Menjalankan Laravel Server..."
    exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
fi
