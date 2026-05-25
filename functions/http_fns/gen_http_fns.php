<?php

// mailgun
require ROOT_DIR . '/vendor/autoload.php';

// some function files
require_once COMMON_DIR . '/manage_socket/socket_manage_fns.php';
require_once FNS_DIR . '/common_fns.php';
require_once FNS_DIR . '/http_fns/check_local_fn.php';
require_once HTTP_FNS . '/http_data_fns.php';
require_once HTTP_FNS . '/query_fns.php';
require_once HTTP_FNS . '/rand_crypt/random_str.php';

// some queries
require_once QUERIES_DIR . '/epic_upgrades.php';
require_once QUERIES_DIR . '/guilds.php';
require_once QUERIES_DIR . '/levels.php';
require_once QUERIES_DIR . '/pr2.php';
require_once QUERIES_DIR . '/users.php';
require_once QUERIES_DIR . '/tokens.php';
