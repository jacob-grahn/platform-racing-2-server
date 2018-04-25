<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/servers/servers_select.php';
require_once __DIR__ . '/../queries/servers/server_update_address.php';

$pdo = pdo_connect();
$servers = servers_select($pdo);
$addresses = array('45.76.24.255'); // todo: this should be in the db

foreach ($servers as $server) {
    if ($server->status == 'down') {
        $fallback_address = $addresses[ array_rand($addresses) ];
        server_update_address($pdo, $server->server_id, $fallback_address);
    }
}

output('result=ok');
