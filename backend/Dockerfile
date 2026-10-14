FROM dunglas/frankenphp:php8.5 AS base

WORKDIR /app

# Install required PHP extensions cleanly using the built-in helper script
RUN install-php-extensions \
      pdo_pgsql \
      mbstring \
      intl \
      zip \
      gd \
      xml \
      opcache

# Copy Composer binary from official image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer


# ── STAGE 2: Composer Dependencies ──────────────────────────────────────
FROM base AS vendor

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --prefer-dist \
      --optimize-autoloader --no-scripts


# ── STAGE 3: Production Runner ──────────────────────────────────────────
FROM base AS runner

# Copy vendor folder from Stage 2
COPY --from=vendor /app/vendor /app/vendor

# Copy the rest of the Laravel application source code
COPY . /app

# Set proper permissions for Laravel storage and cache directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port 80 for HTTP traffic
EXPOSE 80

# Run FrankenPHP in embedded PHP server mode pointing to Laravel's public directory
CMD ["frankenphp", "php-server", "--root", "/app/public"]