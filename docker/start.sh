#!/bin/sh
set -e

PORT="${PORT:-8000}"

echo "=== Setting up Nginx port: $PORT ==="

cat > /etc/nginx/http.d/default.conf << NGINX
server {
    listen ${PORT};
    server_name _;

    root /app/public;
    index index.php index.html;

    charset utf-8;

    client_max_body_size 64m;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi.conf;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
NGINX

echo "=== Nginx config written ==="
cat /etc/nginx/http.d/default.conf

echo "=== Setting up storage ==="
mkdir -p /app/storage/framework/{sessions,views,cache,testing}
mkdir -p /app/storage/app/public
mkdir -p /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "=== Checking environment ==="
echo "APP_KEY: $(php -r 'echo env("APP_KEY", "NOT SET");' 2>/dev/null || echo 'NOT SET')"
echo "APP_ENV: $(php -r 'echo env("APP_ENV", "NOT SET");' 2>/dev/null || echo 'NOT SET')"
echo "DB_HOST: $(php -r 'echo env("DB_HOST", "NOT SET");' 2>/dev/null || echo 'NOT SET')"

echo "=== Testing PHP-FPM (creating test.php) ==="
echo '<?php echo "PHP-FPM OK";' > /app/public/fpm-test.php
chown www-data:www-data /app/public/fpm-test.php

echo "=== Caching config ==="
php /app/artisan config:cache --no-interaction 2>&1 || echo "WARNING: config cache failed"
php /app/artisan route:cache --no-interaction 2>&1 || echo "WARNING: route cache failed"
php /app/artisan view:cache --no-interaction 2>&1 || echo "WARNING: view cache failed"
php /app/artisan event:cache --no-interaction 2>&1 || echo "WARNING: event cache failed"

echo "=== Running migrations ==="
php /app/artisan migrate --force --no-interaction 2>&1 || \
    echo "WARNING: Migration failed — will retry on next deploy"

echo "=== Starting supervisor ==="
exec /usr/bin/supervisord -c /etc/supervisord.conf
