<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/shell_output_fns.php';
require_once __DIR__ . '/../queries/servers/servers_select.php';

//--- load all servers
$pdo = pdo_connect();
$servers = servers_select($pdo);

//--- test all active servers at this address
foreach ($servers as $server) {
    output("Shutting down $server->server_name ($server->server_id)");
    try {
        $reply = talk_to_server($server->address, $server->port, $server->salt, 'shut_down`', true);
        output("Reply: $reply");
    } catch (Exception $e) {
        output($e->getMessage());
    }

    output('');
}


//--- tell it to the world
output('done');
