# start from an official php image
FROM php:7.3-apache

# copy in php code
COPY config.php /pr2/
COPY common/ /pr2/common
COPY functions/ /pr2/functions
COPY http_server/ /pr2/http_server
COPY http_server/ /var/www/html/
COPY vend/ /pr2/vend
COPY common/env.example.php /pr2/common/env.php

# copy in custom config
COPY docker/prepend_file.ini $PHP_INI_DIR/conf.d/

# use default production ini file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# install extensions
RUN pecl install apcu \
    && docker-php-ext-enable apcu
