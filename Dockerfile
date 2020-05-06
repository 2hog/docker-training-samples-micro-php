FROM php:7.2-fpm

WORKDIR /usr/src/app

RUN apt-get update && apt-get install -y zip unzip

RUN curl -fSsl https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions zip xdebug pgsql

COPY ./ ./

