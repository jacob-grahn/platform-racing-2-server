<?php

require_once QUERIES_DIR . '/servers/server_select.php';
require_once COMMON_DIR . '/manage_socket/socket_manage_fns.php';

@$server_id = (int) $argv[1];

if (!isset($server_id)) {
    die(output('Invalid server ID.'));
}

// connect
$pdo = pdo_connect();

// get server
$server = server_select($pdo, $server_id);

// restart it
restart_server(PR2_ROOT . '/pr2.php', $server->address, $server->port, $server->salt, $server->server_id);