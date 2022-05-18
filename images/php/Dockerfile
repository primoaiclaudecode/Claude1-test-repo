FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
            curl \
            wget \
            gettext \
            libfreetype6-dev \
                    libjpeg62-turbo-dev \
                    libpng-dev

RUN docker-php-ext-install pdo_mysql pdo mysqli
RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis


WORKDIR /var/www
CMD ["php-fpm"]