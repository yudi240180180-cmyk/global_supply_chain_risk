#!/bin/bash

set -e

export PORT=3000
echo "==> PORT = $PORT"

# Write nginx config
cat > /etc/nginx/nginx.conf <<'NGINXCONF'
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
        listen 3000;
        server_name _;
        root /var/www/html/public;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
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

# Clear caches
echo "==> Clearing all caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Cache AFTER clearing
php artisan config:cache || true
php artisan view:cache || true

echo "==> Routes will be loaded dynamically (no route:cache in production)"

# Ensure SQLite database file and directory permissions
echo "==> Setting permissions for SQLite and storage..."
mkdir -p /var/www/html/database 2>/dev/null || true
touch /var/www/html/database/database.sqlite 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database 2>/dev/null || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database 2>/dev/null || true

# Migrate database schema
echo "==> Running migrations..."
php artisan migrate --force || echo "Migrations done"

# Run background initial seed and sync so Nginx starts immediately (health check passes)
(
    sleep 5
    echo "==> Running background seeding..."
    php artisan db:seed --force || echo "Seeding complete"
    php artisan sync:all --skip-external || echo "Sync complete"
) &

# Supervisord
cat > /etc/supervisord.conf <<'SUPCONF'
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/tmp/supervisord.pid

[program:php-fpm]
command=/usr/local/sbin/php-fpm --nodaemonize
autostart=true
autorestart=true
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0

[program:scheduler]
command=bash -c "while true; do php /var/www/html/artisan schedule:run >> /dev/stdout 2>&1; sleep 60; done"
autostart=true
autorestart=true
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
user=www-data

[program:queue-worker]
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
user=www-data
SUPCONF

echo "==> Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
