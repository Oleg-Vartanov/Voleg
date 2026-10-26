FROM php:8.5-fpm AS php

WORKDIR /var/www/html/backend

RUN apt-get update \
    && apt-get install -y zlib1g-dev g++ libicu-dev libzip-dev nano unzip \
    && docker-php-ext-install intl pdo pdo_mysql zip bcmath

RUN --mount=type=bind,from=ghcr.io/php/pie:bin,source=/pie,target=/usr/local/bin/pie \
    pie install apcu/apcu xdebug/xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM php AS php_supervisor

RUN apt-get update \
    && apt-get install -y supervisor;

# https://symfony.com/doc/current/messenger.html#graceful-shutdown
RUN docker-php-ext-install pcntl
