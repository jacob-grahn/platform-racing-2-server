<?php

if (!defined(ROOT_DIR)) {
    require_once __DIR__ . '/../../config.php'; // env
}

require_once FNS_DIR . '/common_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once COMMON_DIR . '/manage_socket/socket_manage_fns.php';

@$server_id = (int) $argv[1];

$date = date('r');
output("Starting a happy hour on server #$server_id on $date...");

try {
    // connect
    $pdo = pdo_connect();

    // get server
    $server = server_select($pdo, $server_id);

    // talk to the server
    output("Activating happy hour on $server->server_name ($server->server_id)...");
    talk_to_server($server->address, $server->port, $server->salt, 'activate_happy_hour`', true);
} catch (Exception $e) {
    output($e->getMessage());
}
