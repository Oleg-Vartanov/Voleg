FROM php:8.4-fpm as php

WORKDIR /var/www/html/backend

RUN apt-get update \
    && apt-get install -y zlib1g-dev g++ libicu-dev libzip-dev nano \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip \
    && pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM php AS php_supervisor

RUN apt-get update \
    && apt-get install -y supervisor;

# https://symfony.com/doc/current/messenger.html#graceful-shutdown
RUN docker-php-ext-install pcntl
