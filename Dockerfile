FROM php:8.4-cli-alpine

RUN apk add --no-cache yaml-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install yaml \
    && docker-php-ext-enable yaml \
    && apk del .build-deps

COPY composer.json composer.lock /app/
WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --no-interaction --optimize-autoloader \
    && rm /usr/local/bin/composer

COPY bin/ /app/bin/
COPY src/ /app/src/

RUN apk add --no-cache tini

ENTRYPOINT ["tini", "--", "php", "/app/bin/database-assistant-mcp-server"]
