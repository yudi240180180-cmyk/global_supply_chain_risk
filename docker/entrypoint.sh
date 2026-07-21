#!/bin/bash

echo "==> Starting Laravel on port ${PORT:-8080}..."
export PORT=${PORT:-8080}

# Write nginx config dynamically with correct port
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

echo "==> Nginx config written for port $PORT"

# Clear Laravel caches
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (non-fatal)
echo "==> Running migrations..."
php artisan migrate --force && echo "Migrations done." || echo "WARNING: Migrations failed, check DB_URL."

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Start PHP-FPM in background
echo "==> Starting PHP-FPM..."
php-fpm -D
sleep 2

# Start Nginx in foreground (keeps container alive)
echo "==> Starting Nginx on port $PORT..."
exec nginx -g "daemon off;"
