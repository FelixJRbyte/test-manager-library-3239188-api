FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        libpq-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
    && chmod +x artisan docker/entrypoint.sh

ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
