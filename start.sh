#!/bin/bash
set -e

echo "=== Memulai Inisialisasi Aplikasi TKJ RAG Chatbot ==="

# 1. Pastikan seluruh folder storage & cache tersedia (penting saat persistent volume baru dipasang)
mkdir -p storage/app/private/modules
mkdir -p storage/app/private/rag-staging
mkdir -p storage/app/public/avatars
mkdir -p storage/app/public/pengembang
mkdir -p storage/framework/{sessions,views,cache/data,testing} storage/logs bootstrap/cache

# 2. Salin materi siswa bersih bawaan & foto pengembang jika belum ada di volume
if [ -d "resources/default-modules" ]; then
    echo "Sinkronisasi berkas modul pembelajaran bawaan..."
    cp -n resources/default-modules/* storage/app/private/modules/ 2>/dev/null || true
fi

if [ -d "resources/default-pengembang" ]; then
    echo "Sinkronisasi aset profil pengembang..."
    cp -n resources/default-pengembang/* storage/app/public/pengembang/ 2>/dev/null || true
fi

# Pastikan izin tulis untuk web server (www-data) & worker
if [ "$(id -u)" = "0" ] && id www-data >/dev/null 2>&1; then
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 3. Jalankan migrasi database jika tidak dinonaktifkan
if [ "$RAILPACK_SKIP_MIGRATIONS" != "true" ] && [ "$SKIP_STARTUP_MIGRATIONS" != "true" ]; then
    echo "Menjalankan migrasi database..."
    php artisan migrate --force
fi

# 4. Link storage dan optimasi cache aplikasi
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize

# Pastikan ulang kepemilikan file cache setelah artisan optimize
if [ "$(id -u)" = "0" ] && id www-data >/dev/null 2>&1; then
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi

# 5. Jalankan Worker Antrean AI (RAG Queue) dengan loop autorestart jika diaktifkan
if [ "${RUN_EMBEDDED_WORKER:-true}" = "true" ]; then
    echo "Menjalankan Supervised Worker Antrean AI RAG (queue:work rag)..."
    if [ "$(id -u)" = "0" ] && id www-data >/dev/null 2>&1; then
        (
            while true; do
                su -s /bin/bash www-data -c "php /app/artisan queue:work rag --queue=rag --sleep=3 --tries=3 --timeout=300 --max-time=3600" || true
                sleep 3
            done
        ) &
    else
        (
            while true; do
                php artisan queue:work rag --queue=rag --sleep=3 --tries=3 --timeout=300 --max-time=3600 || true
                sleep 3
            done
        ) &
    fi
fi

# 6. Jalankan Server Web Produksi pada PORT dinamis Railway
LISTEN_PORT="${PORT:-8000}"

if command -v apache2-foreground >/dev/null 2>&1; then
    echo "Menjalankan Apache Production Server pada port ${LISTEN_PORT}..."
    # Remove any conflicting MPM symlinks at runtime (prevents AH00534 under Podman/overlayfs whiteouts)
    rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
    sed -ri "s/Listen [0-9]+/Listen ${LISTEN_PORT}/g" /etc/apache2/ports.conf
    sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${LISTEN_PORT}>/g" /etc/apache2/sites-available/000-default.conf
    exec apache2-foreground
elif command -v docker-php-entrypoint >/dev/null 2>&1 && [ -f "/Caddyfile" ]; then
    echo "Menjalankan FrankenPHP Server..."
    exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
else
    echo "Menjalankan Laravel Server pada port ${LISTEN_PORT}..."
    exec php artisan serve --host=0.0.0.0 --port="${LISTEN_PORT}"
fi
