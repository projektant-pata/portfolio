FROM php:8.5-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# pest-plugin-browser requires ext-sockets, and uses SIGTERM (ext-pcntl) to stop
# the Playwright server it spawns.
RUN chmod +x /usr/local/bin/install-php-extensions \
    && apt-get update && apt-get install -y git curl zip unzip \
    && install-php-extensions pdo_pgsql mbstring zip opcache sockets pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Node powers Playwright, which pest-plugin-browser drives for browser tests.
# Taken from the trixie-slim image so it matches this image's Debian release.
COPY --from=node:24-trixie-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-trixie-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Browsers land outside /var/www so the bind mount can't shadow them, and are
# installed as root here because the test process cannot apt-get at runtime.
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
RUN npx --yes playwright@1.61.1 install --with-deps chromium \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
