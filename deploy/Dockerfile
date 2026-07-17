# Produktions-Image der Nidula-API (ADR-0025): schlankes php-fpm, Code fest
# eingebacken (Deploy = neues Image, keine Änderungen am Server), läuft als
# www-data (nicht Root). Caddy hängt per FastCGI davor.

FROM composer:2 AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs
COPY backend/ ./
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

FROM php:8.5-fpm-alpine
# gd (jpeg/webp/png/freetype) für die Bild-Pipeline, exif fürs Aufnahmedatum
# (VOR dem Strip, ADR-0015), pdo_mysql, intl, pcntl für den Queue-Worker,
# redis (Queue/Cache) als PECL-Extension.
RUN apk add --no-cache freetype libjpeg-turbo libpng libwebp icu-libs \
    && apk add --no-cache --virtual .build $PHPIZE_DEPS \
        freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev icu-dev linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql exif pcntl intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build

COPY deploy/php.prod.ini /usr/local/etc/php/conf.d/zz-nidula.ini

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app ./
RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
