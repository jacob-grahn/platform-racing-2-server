<?php

require_once COMMON_DIR . '/manage_socket/socket_manage_fns.php';
require_once QUERIES_DIR . '/servers/server_select.php';

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
} finally {
    die();
}