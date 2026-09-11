# syntax=docker/dockerfile:1
#
# Image produksi untuk Sistem Informasi Masjid An-Nur.
#
# Dirancang untuk dijalankan di Coolify: FrankenPHP melayani aplikasi di dalam
# container lewat HTTP biasa, sementara TLS dan routing diurus proxy Coolify di
# depannya. Karena itu HTTPS bawaan FrankenPHP dimatikan — bila dibiarkan, ia
# akan mencoba menerbitkan sertifikatnya sendiri dan bentrok dengan proxy.

# ---------------------------------------------------------------------------
# Tahap 1 — bangun aset frontend
#
# `public/build` sengaja tidak ikut di repo (lihat .gitignore), jadi harus
# dibangun di sini. Tahap terpisah agar Node tidak ikut terbawa ke image akhir.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS aset

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
RUN npm ci

COPY resources resources
RUN npm run build

# ---------------------------------------------------------------------------
# Tahap 2 — dependency PHP
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# `--no-scripts` karena skrip Laravel memanggil artisan, sedangkan kode
# aplikasinya belum disalin pada langkah ini.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ---------------------------------------------------------------------------
# Tahap 3 — runtime
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.4-bookworm

WORKDIR /app

# Varian Debian, bukan Alpine: itu yang disarankan dokumentasi FrankenPHP, dan
# ICU untuk ekstensi intl lebih mulus di glibc daripada musl.

# Ekstensi yang benar-benar dituntut dependency terpasang. `intl` diminta
# filament/support (tanpa itu admin panel gagal dimuat sama sekali), `zip` dan
# `xmlreader` diminta openspout untuk export XLSX. `opcache` murni performa.
RUN install-php-extensions \
        intl \
        zip \
        pdo_mysql \
        opcache

# Kode aplikasi lebih dulu, lalu hasil kedua tahap di atas ditimpakan. Urutan
# ini disengaja: kalau dibalik, vendor dan public/build hanya selamat selama
# keduanya masih tercantum di .dockerignore — ketergantungan tersembunyi yang
# akan patah tanpa suara begitu berkas itu disunting.
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=aset /app/public/build ./public/build

COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Matikan HTTPS bawaan FrankenPHP: TLS diselesaikan proxy Coolify.
ENV SERVER_NAME=":80"

# Setelan PHP untuk produksi. `upload_max_filesize` dan `post_max_size` harus
# selaras dengan batas unggahan di proxy, kalau tidak berkas besar gagal tanpa
# pesan yang jelas.
RUN { \
        echo "opcache.enable=1"; \
        echo "opcache.validate_timestamps=0"; \
        echo "opcache.memory_consumption=128"; \
        echo "upload_max_filesize=20M"; \
        echo "post_max_size=20M"; \
        echo "memory_limit=256M"; \
    } > /usr/local/etc/php/conf.d/masjid.ini

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
