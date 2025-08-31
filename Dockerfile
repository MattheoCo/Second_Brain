FROM php:8.3-cli-alpine

RUN set -eux; \
    apk add --no-cache bash git unzip icu-dev postgresql-dev; \
    docker-php-ext-install -j$(nproc) intl pdo_pgsql opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Allow Composer as root in non-interactive PaaS
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy application sources (so composer.json is present at runtime)
COPY . .

# Entrypoint script
COPY scripts/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

# Default to production-safe settings; override locally if needed
ENV APP_ENV=prod \
    APP_DEBUG=0

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
