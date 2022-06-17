# Start from an official php image
FROM php:7.3-cli

# Copy in php code
COPY config.php /pr2/
COPY common/ /pr2/common
COPY common/env.example.php /pr2/common/env.php
COPY functions/ /pr2/functions
COPY multiplayer_server/ /pr2/multiplayer_server
COPY vend/ /pr2/vend

# Copy in custom config
COPY docker/prepend_file.ini $PHP_INI_DIR/conf.d/

# install extensions
RUN docker-php-ext-install pdo_mysql sockets