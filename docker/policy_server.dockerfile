# Start from an official php image
FROM php:7.3-cli

# Copy in php code
COPY config.php /pr2/
COPY common/ /pr2/common
COPY common/env.example.php /pr2/common/env.php
COPY policy_server/ /pr2/policy_server
COPY vend/ /pr2/vend

# Copy in custom config
COPY docker/prepend_file.ini $PHP_INI_DIR/conf.d/

# install extensions
RUN docker-php-ext-install pdo_mysql sockets

# Run the policy server
CMD ["php", "/pr2/policy_server/run_policy.php"]