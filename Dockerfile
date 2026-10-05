FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json ./
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm install --no-audit --no-fund \
    && npm run build

FROM serversideup/php:8.5-fpm-nginx

USER root

WORKDIR /var/www/html

COPY . .
COPY --from=frontend /app/public/build ./public/build

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    NGINX_WEBROOT=/var/www/html/public \
    AUTORUN_ENABLED=true \
    PHP_OPCACHE_ENABLE=1

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
