<?php

// all fns
require_once GEN_HTTP_FNS;
require_once FNS_DIR . '/cron/cron_fns.php';

// ensure part awards
require_once QUERIES_DIR . '/part_awards.php';

// speak to servers (campaign prizes update), remove expired servers
require_once QUERIES_DIR . '/campaigns.php';
require_once QUERIES_DIR . '/servers.php';

// tell the command line
$time = date('r');
output("Hourly CRON starting at $time...");

// connect
$pdo = pdo_connect();

try {
    // servers_deactivate_expired($pdo);
    servers_delete_old($pdo);
    ensure_awards($pdo);
    generate_level_list($pdo, 'newest');
    generate_level_list($pdo, 'best');
    generate_level_list($pdo, 'best_week');
    generate_level_list($pdo, 'campaign');
    set_campaign($pdo);

    // tell the command line
    output('Hourly CRON successful.');
} catch (Exception $e) {
    output('ERROR: Hourly CRON failed.');
}
