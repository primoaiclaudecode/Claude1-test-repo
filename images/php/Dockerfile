FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
            curl \
            wget \
            gettext \
            build-essential \
            git \
            curl \
            libssl-dev \
            zlib1g-dev \
            libpng-dev \
            libjpeg-dev \
            libfreetype6-dev \
            libonig-dev \
            libxml2-dev \
            libicu-dev \
            zip \
            unzip \
            libzip-dev \
            libfreetype6-dev \
            libwebp-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
            nano \
            libgmp-dev \
            libldap2-dev \
            netcat \
            sqlite3 \
            libsqlite3-dev \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
            && docker-php-ext-configure gd --with-freetype --with-jpeg \
            && docker-php-ext-install -j$(nproc) gd \
            &&  docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-webp-dir=/usr/include/  --with-jpeg-dir=/usr/include/  \
            && docker-php-ext-install gd pdo pdo_mysql pdo_sqlite zip gmp bcmath pcntl ldap sysvmsg exif

RUN docker-php-ext-install pdo_mysql pdo mysqli mbstring zip
RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis


WORKDIR /var/www
CMD ["php-fpm"]