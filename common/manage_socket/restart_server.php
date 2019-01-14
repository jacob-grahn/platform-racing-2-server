<?php

if (!defined(ROOT_DIR)) {
    require_once __DIR__ . '/../../config.php'; // env
}

require_once FNS_DIR . '/common_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once COMMON_DIR . '/manage_socket/socket_manage_fns.php';

@$server_id = (int) $argv[1];

if ($server_id > 0) {
    // connect
    $pdo = pdo_connect();

    // get server
    $server = server_select($pdo, $server_id);

    // restart it
    restart_server(PR2_ROOT . '/pr2.php', $server->address, $server->port, $server->salt, $server->server_id);
} else {
    output('No server ID was passed to restart_server.php');
}
