<?php

$directory = __DIR__; // this directory

define('ROOT_DIR', $directory); // root
define('COMMON_DIR', $directory . '/common'); // common
define('QUERIES_DIR', $directory . '/common/queries'); // queries

define('PR2_ROOT', $directory . '/multiplayer_server'); // socket server root
define('PR2_FNS_DIR', $directory . '/multiplayer_server/fns'); // socket server fns

define('HTTP_FNS', $directory . '/functions/http_fns'); // http_server/fns
define('GEN_HTTP_FNS', $directory . '/functions/http_fns/gen_http_fns.php'); // http_server/fns
define('WWW_ROOT', $directory . '/http_server'); // main folder

define('SOCKET_DAEMON_FILES', $directory . '/vend/socket/index.php'); // files for phpSocketDaemon

define('FRED', 4291976); // fred id
$special_ids = array(FRED, 5321458, 5451130); // fred, sir, clint

// call globally needed files
require_once COMMON_DIR . '/env.php';
require_once COMMON_DIR . '/pdo_connect.php';
require_once COMMON_DIR . '/s3_connect.php';
require_once ROOT_DIR . '/vend/S3.php';

?>
