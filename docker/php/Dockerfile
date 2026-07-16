FROM php:8.5-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
    && apt-get update && apt-get install -y git curl zip unzip \
    && install-php-extensions pdo_pgsql mbstring zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
