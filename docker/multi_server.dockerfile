# Start from an official php image
FROM php:8.2-cli

# Install extensions
RUN docker-php-ext-install pdo_mysql sockets

# Copy in php code
COPY config.php /pr2/
COPY common/ /pr2/common
COPY common/env.example.php /pr2/common/env.php
COPY functions/ /pr2/functions
COPY multiplayer_server/ /pr2/multiplayer_server
COPY vend/ /pr2/vend
COPY docker/prepend_file.ini $PHP_INI_DIR/conf.d/

# Run the gameserver
ENTRYPOINT ["php", "pr2/multiplayer_server/pr2.php"]