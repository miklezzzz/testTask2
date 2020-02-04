FROM php:7.2-fpm-alpine

WORKDIR /var/www/simple-api

COPY simple-api /var/www/simple-api
COPY docker/php/php.ini /usr/local/etc/php/php.ini

RUN apk update \
    && apk add  --no-cache git mysql-client curl libmcrypt libmcrypt-dev openssh-client icu-dev \
    libxml2-dev freetype-dev libpng-dev libjpeg-turbo-dev g++ make autoconf \
    && docker-php-source extract \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb \
    && docker-php-source delete \
    && docker-php-ext-install pdo_mysql soap intl zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/* \
    && composer install

CMD ["php-fpm", "-F"]

EXPOSE 9000
