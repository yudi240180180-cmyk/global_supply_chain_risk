#!/bin/bash

set -e

export PORT=${PORT:-8080}
echo "==> PORT = $PORT"

# Write nginx config
cat > /etc/nginx/nginx.conf <<NGINXCONF
user www-data;
worker_processes auto;
error_log /dev/stderr warn;
pid /tmp/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    sendfile on;
    keepalive_timeout 65;
    client_max_body_size 50M;
    access_log /dev/stdout;

    server {
        listen ${PORT};
        server_name _;
        root /var/www/html/public;
        index index.php index.html;

        location / {
            try_files \$uri \$uri/ /index.php?\$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_read_timeout 300;
        }

        location ~ /\.ht {
            deny all;
        }
    }
}
NGINXCONF

echo "==> Nginx config written"

# Laravel cache - clear dulu SEMUA cache sebelum rebuild
echo "==> Clearing all caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "==> DB_CONNECTION = $DB_CONNECTION"
echo "==> DB_URL = $DB_URL"

# Rebuild cache dengan env yang sudah benar
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Migrations (non-fatal)
echo "==> Running migrations..."
php artisan migrate --force && echo "Migrations done." || echo "WARNING: migrate failed"

# Seed database if users table is empty (first deploy)
echo "==> Checking if seeding is needed..."
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tr -d '[:space:]' || echo "0")
echo "==> Current user count: $USER_COUNT"
if [ "$USER_COUNT" = "0" ]; then
    echo "==> Seeding database for first time..."
    php artisan db:seed --force && echo "Seeding done." || echo "WARNING: seeding failed"
else
    echo "==> Database already has users, skipping seed."
fi

# Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Start PHP-FPM
echo "==> Starting PHP-FPM..."
/usr/local/sbin/php-fpm --nodaemonize &
PHP_PID=$!
echo "==> PHP-FPM PID: $PHP_PID"

# Wait for PHP-FPM socket
sleep 3

# Test PHP-FPM is running
if kill -0 $PHP_PID 2>/dev/null; then
    echo "==> PHP-FPM is running"
else
    echo "ERROR: PHP-FPM failed to start!"
    exit 1
fi

# Start Nginx
echo "==> Starting Nginx on port $PORT..."
exec nginx -g "daemon off;"
