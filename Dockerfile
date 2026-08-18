# syntax=docker/dockerfile:1

# ---- Stage 1: PHP dependencies (cached by composer.lock) ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# ---- Stage 2: Frontend build (cached by package-lock.json) ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ---- Stage 3: Runtime image (nginx + php-fpm in one container via supervisord) ----
FROM php:8.2-fpm-alpine AS app

RUN apk add --no-cache nginx supervisor icu-libs oniguruma \
    && apk add --no-cache --virtual .build-deps icu-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath intl opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/nginx/app.conf /etc/nginx/http.d/default.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-custom.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
