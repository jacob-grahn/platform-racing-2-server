FROM php:8.2-apache

# Copy in php code
COPY config.php /pr2/
COPY composer.json /pr2/
COPY composer.lock /pr2/
COPY common/ /pr2/common
COPY functions/ /pr2/functions
COPY http_server/ /pr2/http_server
COPY vend/ /pr2/vend
COPY common/env.example.php /pr2/common/env.php
COPY docker/http_server_startup.sh /http_server_startup.sh

# Copy in custom config
COPY docker/prepend_file.ini $PHP_INI_DIR/conf.d/

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Move web root
ENV APACHE_DOCUMENT_ROOT=/pr2/http_server
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip

# Install extensions
RUN docker-php-ext-install pdo_mysql

# Install pecl extensions
RUN pear config-set php_ini "$PHP_INI_DIR/php.ini" \
    && pecl install apcu-5.1.23 \
    && docker-php-ext-enable apcu

# Install composer dependencies
RUN cd /pr2 \
    && curl -sS https://getcomposer.org/installer | php \
    && php composer.phar install --no-dev --optimize-autoloader

# Run minute and hour cron when this service starts up to generate server and level list files
ENTRYPOINT []
CMD [ "/http_server_startup.sh" ]