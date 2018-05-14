<?php

/*

Post this in the pull request:



**_This pull requires a server configuration edit._**
Without this change, EVERY FILE in platform-racing-2-server will break.

The goal is to tell nginx to load CONSTANTS.php every time a php command or client request is made.
Add the following line within the "location ~ \.php$" block of your server configuration:

```
fastcgi_param PHP_VALUE "auto_prepend_file=/full/path/to/this/file/config.php";
```

Replace "/full/path/to/this/file" with the full path to config.php (this file).

For more information, see these resources:
https://stackoverflow.com/questions/26192274/replacement-for-php-htaccess-values-in-nginx
https://stackoverflow.com/questions/14884439/automatically-load-a-config-php-file-for-all-pages-before-anything-else

*/

$directory = __DIR__; // this directory

define('ROOT_DIR', $directory); // root
define('COMMON_DIR', $directory . '/common'); // common
define('QUERIES_DIR', $directory . '/common/queries'); // queries

define('PR2_ROOT', $directory . '/multiplayer_server'); // socket server root
define('PR2_FNS_DIR', $directory . '/multiplayer_server/fns'); // socket server fns

define('HTTP_FNS', $directory . '/http_server/fns'); // http_server/fns
define('WWW_ROOT', $directory . "/http_server/www"); // main folder

define('SOCKET_DAEMON_FILES', $directory . '/vend/socket/index.php'); // files for phpSocketDaemon

// call globally needed files
require_once COMMON_DIR . '/env.php';
require_once COMMON_DIR . '/pdo_connect.php';
require_once COMMON_DIR . '/s3_connect.php';
require_once ROOT_DIR . '/vend/S3.php';

?>