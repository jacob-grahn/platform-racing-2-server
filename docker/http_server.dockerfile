FROM php:8.2-fpm

# copy in php code
COPY config.php /pr2/
COPY common/ /pr2/common
COPY functions/ /pr2/functions
COPY http_server/ /pr2/http_server
COPY vend/ /pr2/vend
COPY common/env.example.php /pr2/common/env.php

# copy in custom config
COPY docker/prepend_file.ini $PHP_INI_DIR/conf.d/

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# install extensions
RUN docker-php-ext-install pdo_mysql

# install pecl extensions
RUN pear config-set php_ini "$PHP_INI_DIR/php.ini" \
    && pecl install apcu-5.1.23 \
    && docker-php-ext-enable apcu