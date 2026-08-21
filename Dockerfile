FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY . .

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
