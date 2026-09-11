#!/bin/sh
set -e

# Persiapan tiap kali container dijalankan.
#
# Semuanya dikerjakan di sini, bukan saat build image, karena bergantung pada
# variabel environment dan database yang baru tersedia saat runtime.

# ---------------------------------------------------------------------------
# Folder storage
#
# Volume persisten dipasang di storage/app/public dan mulanya kosong, sementara
# sisa struktur storage/ ikut terbawa image. Folder-folder ini dibuat ulang
# untuk berjaga bila ada mount yang lebih luas daripada seharusnya — tanpa
# framework/views dan framework/sessions, aplikasi mati dengan pesan yang
# membingungkan.
# ---------------------------------------------------------------------------
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R 775 storage bootstrap/cache

# Tautan public/storage → storage/app/public. Dibuat ulang tiap start karena
# container baru selalu kehilangan symlink-nya.
php artisan storage:link --force

# ---------------------------------------------------------------------------
# Aset admin panel
#
# CSS dan JS Filament tinggal di public/css/filament dan public/js/filament,
# dan keduanya di-gitignore — jadi tidak pernah ikut ke repo maupun ke build
# context. Normalnya dibuat ulang oleh `filament:upgrade` lewat composer, tapi
# itu berjalan di tahap `vendor` dan hasilnya ikut terbuang bersama tahap itu:
# runtime hanya menyalin /app/vendor, sedangkan asetnya lahir di /app/public.
#
# Akibatnya admin panel tampil sebagai HTML tanpa gaya sama sekali — tampak
# rusak parah padahal aplikasinya sehat.
# ---------------------------------------------------------------------------
php artisan filament:assets

# ---------------------------------------------------------------------------
# Database
#
# Migrasi dijalankan otomatis agar redeploy tidak menyisakan skema tertinggal.
# Aman diulang: migrasi yang sudah jalan akan dilewati.
# ---------------------------------------------------------------------------
php artisan migrate --force --no-interaction

# ---------------------------------------------------------------------------
# Cache produksi
#
# Wajib di sini, bukan saat build: config:cache membekukan nilai environment,
# dan environment baru lengkap setelah Coolify menyuntikkannya.
# ---------------------------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

exec "$@"
