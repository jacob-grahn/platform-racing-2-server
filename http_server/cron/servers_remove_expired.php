<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/servers/servers_deactivate_expired.php';
require_once __DIR__ . '/../queries/servers/servers_delete_old.php';

$pdo = pdo_connect();

servers_deactivate_expired($pdo);
servers_delete_old($pdo);

echo 'result=ok';
