#!/bin/bash
set -e

echo "==> Starting Laravel deployment..."

# Railway injects $PORT dynamically, default to 8080
export PORT=${PORT:-8080}
echo "==> Using port: $PORT"

# Write nginx config with correct port
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

        gzip on;
        gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

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

        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }
}
NGINXCONF

echo "==> Nginx config written for port $PORT"

# Laravel setup
echo "==> Running Laravel setup..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force || echo "WARNING: Migration failed, continuing anyway..."

# Set storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM in background
echo "==> Starting PHP-FPM..."
php-fpm -D

# Wait for PHP-FPM to be ready
sleep 2

# Start Nginx in foreground
echo "==> Starting Nginx on port $PORT..."
nginx -g "daemon off;"
