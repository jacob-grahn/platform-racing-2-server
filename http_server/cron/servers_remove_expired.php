<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/servers/servers_deactivate_expired.php';
require_once __DIR__ . '/../queries/servers/servers_delete_old.php';

// tell the command line
$time = date('r');
output("Remove expired servers CRON starting at $time...");

// connect
$pdo = pdo_connect();

servers_deactivate_expired($pdo);
servers_delete_old($pdo);

// tell the command line
output('Removed all expired servers.');
