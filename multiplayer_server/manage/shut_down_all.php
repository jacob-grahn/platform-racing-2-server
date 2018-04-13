<?php

require_once __DIR__ . '/../../env.php';
require_once __DIR__ . '/management_fns.php';
require_once __DIR__ . '/../../http_server/fns/pdo_connect.php';
require_once __DIR__ . '/../../http_server/queries/servers/servers_select.php';

$day = date('w');

@$mode = $argv[1];
if (!isset($mode)) {
    $mode = 'all';
}

output('shutting down servers... ');


//--- load all servers
$pdo = pdo_connect();
$servers = servers_select($pdo);


//--- test all active servers at this address
foreach ($servers as $server) {
    output($server->server_name);

    if (($mode == 'inactive' && $server->active == 0) ||
        ($mode == 'active' && $server->active == 1) ||
        ($mode == 'all')
    ) {
        echo "Shutting down $server->server_name ($server->server_id)";
        try {
            $reply = talk_to_server('localhost', $server->port, $server->salt, 'shut_down`', true);
            echo "Reply: $reply\n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    output('');
    sleep(1);
}


//--- tell it to the world
output('done!');
