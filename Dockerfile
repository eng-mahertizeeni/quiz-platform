FROM php:8.4-fpm-alpine

ARG APP_ENV=production
ARG APP_DEBUG=false

ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=${APP_DEBUG} \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN apk add --no-cache \
    nginx supervisor curl \
    libpng libjpeg-turbo freetype libwebp libxpm \
    oniguruma libxml2 libzip icu \
    npm mysql-client postgresql-client \
    && apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libxpm-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql pdo_pgsql mbstring xml bcmath gd zip intl opcache exif \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

RUN cp .env.example .env && \
    php artisan key:generate --force --no-interaction --quiet && \
    rm .env

RUN npm install --ignore-scripts --no-audit --no-fund && \
    npm run build && \
    rm -rf node_modules

RUN php artisan storage:link --force || true

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh

RUN chmod +x /start.sh

EXPOSE 8000

CMD ["/start.sh"]