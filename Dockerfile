FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    bash curl git unzip \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev oniguruma-dev icu-dev \
    ttf-freefont fontconfig

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_mysql mbstring zip exif gd intl pcntl bcmath opcache

# Redis extension (build deps temporales para pecl)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del --no-network .build-deps

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Non-root user
RUN addgroup -g 1000 www \
 && adduser -u 1000 -G www -s /bin/sh -D www

COPY --chown=www:www . .

USER www

EXPOSE 9000
CMD ["php-fpm"]
