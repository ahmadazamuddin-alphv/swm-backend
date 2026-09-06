FROM php:8.3-cli-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install intl pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

FROM php-base AS php-dependencies

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

FROM node:22-bookworm-slim AS frontend-assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php-base AS runtime

COPY . .
COPY --from=php-dependencies /app/vendor ./vendor
COPY --from=frontend-assets /app/node_modules ./node_modules
COPY --from=frontend-assets /app/public/build ./public/build
COPY deploy/start-container.sh /usr/local/bin/start-container

RUN chmod +x /usr/local/bin/start-container \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8080

CMD ["start-container"]
