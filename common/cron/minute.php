<?php

require_once GEN_HTTP_FNS;
require_once FNS_DIR . '/cron/cron_fns.php';
require_once HTTP_FNS . '/rand_crypt/PseudoRandom.php';
require_once QUERIES_DIR . '/artifact_location.php';
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/gp.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/servers.php';

// tell the command line
$time = date('r');
output("Minute CRON starting at $time...");

// connect
$pdo = pdo_connect();

// perform minute tasks
failover_servers($pdo);
check_servers($pdo);
generate_level_list($pdo, 'newest');
update_artifact($pdo);
run_update_cycle($pdo);
write_server_status($pdo);

// tell the command line
output('Minute CRON successful.');
