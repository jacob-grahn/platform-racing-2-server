<?php

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../pdo_connect.php';
require_once __DIR__ . '/../../http_server/queries/servers/server_select.php';
require_once __DIR__ . '/management_fns.php';

$pdo = pdo_connect();

for ($i=1; $i<100; $i++) {
    @$server_id = (int) $argv[$i];

    if (isset($server_id) && $server_id != 0) {
        try {
            $server = server_select($pdo, $server_id);
            $reply = talk_to_server('localhost', $server->port, $server->salt, 'shut_down`', true);
            echo "Shutting down server $server_id. Reply: $reply\n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } else {
        break;
    }
}
