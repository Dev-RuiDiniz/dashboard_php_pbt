FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

COPY --chown=www-data:www-data app/ ./app/
COPY --chown=www-data:www-data config/ ./config/
COPY --chown=www-data:www-data public/ ./public/
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && mkdir -p storage/logs storage/exports storage/uploads \
    && chown -R www-data:www-data storage

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

EXPOSE 80
