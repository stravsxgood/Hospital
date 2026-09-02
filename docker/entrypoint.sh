#!/bin/sh
set -e

# Pastikan permission direktori storage dan cache Laravel sudah sesuai
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate storage link jika belum ada
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

# Jalankan perintah CMD default atau custom command
exec "$@"
