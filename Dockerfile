FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  libzip-dev \
  libicu-dev

RUN docker-php-ext-install pdo_mysql zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html
