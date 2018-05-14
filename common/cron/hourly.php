<?php

// all fns
require_once HTTP_FNS . '/all_fns.php';

// ensure part awards
require_once QUERIES_DIR . '/part_awards/part_awards_select_list.php';
require_once QUERIES_DIR . '/part_awards/part_awards_delete_old.php';
require_once QUERIES_DIR . '/part_awards/ensure_awards.php';

// folding_at_home data select/insert/update from/into/in db
require_once HTTP_FNS . '/cron/fah_fns.php';
require_once QUERIES_DIR . '/folding/folding_insert.php';
require_once QUERIES_DIR . '/folding/folding_select_by_user_id.php';
require_once QUERIES_DIR . '/folding/folding_select_list.php';
require_once QUERIES_DIR . '/folding/folding_update.php';

// message, insert rank token
require_once QUERIES_DIR . '/messages/message_insert.php';
require_once QUERIES_DIR . '/rank_tokens/rank_token_select.php';
require_once QUERIES_DIR . '/rank_tokens/rank_token_upsert.php';

// remove expired servers
require_once QUERIES_DIR . '/servers/servers_deactivate_expired.php';
require_once QUERIES_DIR . '/servers/servers_delete_old.php';

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
