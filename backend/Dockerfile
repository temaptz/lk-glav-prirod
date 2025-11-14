# Backend (Yii2) Dockerfile
FROM php:8.1-fpm-alpine AS base

# --- system deps ------------------------------------------------------------
RUN apk add --no-cache git zip unzip icu-dev libpq-dev bash curl

# --- php extensions ---------------------------------------------------------
RUN docker-php-ext-install intl pdo_pgsql

# --- composer ---------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- app sources ------------------------------------------------------------
WORKDIR /app
COPY composer.json composer.lock* ./

# install dependencies (will download yii2)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# copy rest of the app (if any)
COPY . .

EXPOSE 9000
CMD ["php-fpm"]
