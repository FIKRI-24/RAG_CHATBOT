# Stage 1: Install PHP production dependencies (also provides Laravel pagination views for Tailwind)
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader --ignore-platform-reqs

# Stage 2: Build Vite frontend assets
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY scripts ./scripts
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY --from=composer-deps /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# Stage 3: Production PHP 8.3 + Apache runtime
FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/app/public \
    COMPOSER_ALLOW_SUPERUSER=1 \
    DEBIAN_FRONTEND=noninteractive

# Install PHP extensions (both MySQL & PostgreSQL supported)
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        mbstring \
        bcmath \
        intl \
        opcache \
    && apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules and point DocumentRoot to /app/public
RUN a2enmod rewrite headers \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory /app/public>\n    Options -Indexes +FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\nServerName localhost\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Production PHP tuning for classroom concurrency & DOCX/PDF uploads
RUN printf 'memory_limit=256M\nupload_max_filesize=20M\npost_max_size=25M\nmax_execution_time=90\nopcache.enable=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=16\nopcache.max_accelerated_files=10000\nopcache.validate_timestamps=0\n' > /usr/local/etc/php/conf.d/laravel.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application source, PHP dependencies, and compiled Vite assets
COPY . .
COPY --from=composer-deps /app/vendor /app/vendor
COPY --from=assets /app/public/build /app/public/build

# Generate optimized autoloader, ensure Unix LF line endings on start.sh
RUN composer dump-autoload --optimize --no-dev \
    && sed -i 's/\r$//' /app/start.sh \
    && chmod +x /app/start.sh \
    && mkdir -p storage/app/private/modules storage/app/private/rag-staging storage/app/public/avatars storage/app/public/pengembang storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["/bin/bash", "/app/start.sh"]
