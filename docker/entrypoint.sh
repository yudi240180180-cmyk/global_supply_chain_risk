#!/bin/bash

set -e

# Force port 3000 - Railway may inject PORT=5432 (postgres port) which breaks Nginx
export PORT=3000
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

echo "==> Nginx config written for port $PORT"

# Laravel cache
echo "==> Clearing all caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "==> DB_CONNECTION = $DB_CONNECTION"
echo "==> DB_URL = $DB_URL"

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Migrations
echo "==> Running migrations..."
php artisan migrate --force && echo "Migrations done." || echo "WARNING: migrate failed"

# Seeding & Data Sync (only run ONCE on first deploy)
echo "==> Checking if seeding is needed..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1 || echo "0")
echo "==> Current user count: $USER_COUNT"

if [ "$USER_COUNT" = "0" ]; then
    echo "==> Seeding database for first time..."
    php artisan db:seed --class=AdminUserSeeder --force || echo "AdminUserSeeder failed"
    php artisan db:seed --class=ManagerUserSeeder --force || echo "ManagerUserSeeder failed"
    
    echo "==> Syncing all data (countries, economics, rates, news, weather, ports)..."
    # Run in background to avoid timeout, log to file
    nohup bash -c "
        sleep 10
        php artisan sync:countries >> /var/www/html/storage/logs/sync.log 2>&1
        php artisan sync:economics >> /var/www/html/storage/logs/sync.log 2>&1
        php artisan sync:rates >> /var/www/html/storage/logs/sync.log 2>&1
        php artisan sync:news >> /var/www/html/storage/logs/sync.log 2>&1
        php artisan sync:weather >> /var/www/html/storage/logs/sync.log 2>&1
        php artisan calculate:risk >> /var/www/html/storage/logs/sync.log 2>&1
        echo '==> Initial data sync completed at $(date)' >> /var/www/html/storage/logs/sync.log
    " &
    
    echo "==> Background sync started. Check /storage/logs/sync.log for progress."
else
    echo "==> Users already exist ($USER_COUNT users). Skipping seed & sync."
fi

# Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Write supervisord config with correct port for nginx
cat > /etc/supervisord.conf <<SUPCONF
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

echo "==> Starting all services (nginx + php-fpm + scheduler + queue)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
