<?php

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/management_fns.php';
require_once __DIR__ . '/../http_server/fns/pdo_connect.php';
require_once __DIR__ . '/../http_server/queries/servers/servers_select.php';

@$mode = $argv[1];
if (!isset($mode)) {
    $mode = 'all';
}

output('Shutting down servers...');


// load all servers
$pdo = pdo_connect();
$servers = servers_select($pdo);


// test all servers with mode
foreach ($servers as $server) {
    output("Testing $server->server_name (ID: #$server->server_id)...");

    if (($mode == 'inactive' && $server->active == 0) ||
        ($mode == 'active' && $server->active == 1) ||
        ($mode == 'all')
    ) {
        output("Initiating shutdown of $server->server_name (ID: #$server->server_id)...");
        try {
            $reply = talk_to_server('localhost', $server->port, $server->salt, 'shut_down`', true);
            if (empty($reply)) {
                throw new Exception('ERROR: There was no reply from the server.');
            }
            output($reply);
        } catch (Exception $e) {
            output($e->getMessage());
        } finally {
            output('');
            sleep(1);
        }
    }
}


// tell it to the world
output('All operations completed.');
