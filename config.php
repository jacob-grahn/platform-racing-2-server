<?php

$directory = __DIR__; // this directory

define('ROOT_DIR', $directory); // root
define('CACHE_DIR', $directory . '/cache'); // cached files
define('COMMON_DIR', $directory . '/common'); // common
define('QUERIES_DIR', $directory . '/common/queries'); // queries

define('PR2_ROOT', $directory . '/multiplayer_server'); // socket server root
define('WWW_ROOT', $directory . '/http_server'); // pr2hub root directory folder

define('FNS_DIR', $directory . '/functions'); // fns folder
define('HTTP_FNS', $directory . '/functions/http_fns'); // fns/http_fns
define('PR2_FNS', $directory . '/functions/multi_fns'); // socket server fns
define('GEN_HTTP_FNS', HTTP_FNS . '/gen_http_fns.php');
define('ALL_MULTI_FNS', PR2_FNS . '/all_multi_fns.php');

define('SOCKET_DAEMON_FILES', $directory . '/vend/socket/index.php'); // files for phpSocketDaemon

define('FRED', 4291976); // fred id
$special_ids = [FRED, 5321458, 5451130]; // fred, sir, clint
$group_names = [ // group names defined in client
    ['Guest'],
    ['Member', 'Community Ambassador'],
    ['Temp Mod', 'Trial Moderator', 'Moderator'],
    ['Admin']
];
$group_colors = [ // group colors defined in client
    ['676666'],
    ['047B7B', 'BC9055'], // special user color hardcoded in common_fns.php
    ['006400', '0092FF', '1C369F'],
    ['870A6F']
];


// call globally needed files
require_once COMMON_DIR . '/env.php';
require_once COMMON_DIR . '/pdo_connect.php';
require_once COMMON_DIR . '/s3_connect.php';
require_once ROOT_DIR . '/vend/S3.php';

// activate better error reporting in debug mode
if ($DEBUG_MODE) {
    error_reporting(E_ALL | E_STRICT);
}
