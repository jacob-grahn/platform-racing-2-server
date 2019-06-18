<?php

$directory = __DIR__; // this directory

define('ROOT_DIR', $directory); // root
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
$group_colors = ['676666', '047b7b', '1c369f', '870a6f']; // group colors as defined in the client
$trusted_refs = [ // trusted referrers for the pr2 client
    'http://pr2hub.com/', // pr2hub
    'https://pr2hub.com/', // pr2hub
    'http://www.pr2hub.com/', // pr2hub
    'https://www.pr2hub.com/', // pr2hub
    'http://cdn.jiggmin.com/', // jv
    'http://chat.kongregate.com/', // kong
    'http://external.kongregate-games.com/gamez/', // kong
    'http://game10110.konggames.com/games/Jiggmin/platform-racing-2', // kong
    'http://uploads.ungrounded.net/439000/', // newgrounds
    'https://jiggmin2.com/games/platform-racing-2', // jv2
    'http://naxxol.github.io/' // advanced LE
];

// call globally needed files
require_once COMMON_DIR . '/env.php';
require_once COMMON_DIR . '/pdo_connect.php';
require_once COMMON_DIR . '/s3_connect.php';
require_once ROOT_DIR . '/vend/S3.php';
