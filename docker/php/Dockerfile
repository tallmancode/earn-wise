FROM node:20-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline
COPY resources/ ./resources/
RUN npm run build || echo "No build script"

FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.3-fpm
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
WORKDIR /var/www

# Copy PHP dependencies from composer-deps
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=composer-deps /app/composer.lock ./composer.lock
COPY --from=composer-deps /app/composer.json ./composer.json

# Copy built assets from node-build
COPY --from=node-build /app/resources/ ./resources/
COPY --from=node-build /app/node_modules ./node_modules

# Copy the rest of the application source
COPY . .

# Re-generate autoload and run post-install scripts now that the full app is present
RUN composer dump-autoload --optimize --no-interaction \
    && php artisan package:discover --ansi 2>/dev/null || true

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
