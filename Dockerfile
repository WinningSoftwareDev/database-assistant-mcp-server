FROM php:8.1-cli

# Install required extensions
RUN apt-get update && apt-get install -y \
        libyaml-dev \
    && pecl install yaml \
    && docker-php-ext-enable yaml \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install dependencies first (layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copy application code
COPY bin/ bin/
COPY src/ src/

ENTRYPOINT ["php", "/app/bin/app"]
