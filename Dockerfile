FROM php:8.3-cli-alpine

RUN set -eux; \
    apk add --no-cache bash git unzip icu-dev postgresql-dev; \
    docker-php-ext-install -j$(nproc) intl pdo_pgsql opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Entrypoint script
COPY scripts/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

ENV APP_ENV=dev \
    APP_DEBUG=1

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
