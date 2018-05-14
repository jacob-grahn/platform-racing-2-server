<?php

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