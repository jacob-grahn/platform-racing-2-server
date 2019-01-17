<?php

// all fns
require_once GEN_HTTP_FNS;

// ensure part awards
require_once QUERIES_DIR . '/part_awards.php';

// folding_at_home data select/insert/update from/into/in db
require_once FNS_DIR . '/cron/cron_fns.php';
require_once QUERIES_DIR . '/folding.php';

// message, insert rank token
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/rank_tokens.php';

// remove expired servers
require_once QUERIES_DIR . '/servers.php';

// tell the command line
$time = date('r');
output("Hourly CRON starting at $time...");

// connect
$pdo = pdo_connect();

try {
    generate_level_list($pdo, 'newest');
    generate_level_list($pdo, 'best');
    generate_level_list($pdo, 'best_today');
    generate_level_list($pdo, 'campaign');
    ensure_awards($pdo);
    servers_deactivate_expired($pdo);
    servers_delete_old($pdo);
    fah_update($pdo);

    // tell the command line
    output('Hourly CRON successful.');
} catch (Exception $e) {
    output('ERROR: Hourly CRON failed.');
}
