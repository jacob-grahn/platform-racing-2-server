<?php

// all fns
require_once __DIR__ . '/../fns/all_fns.php';

// ensure part awards
require_once __DIR__ . '/../queries/part_awards/part_awards_select_list.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_delete_old.php';
require_once __DIR__ . '/../queries/part_awards/ensure_awards.php';

// folding_at_home data select/insert/update from/into/in db
require_once __DIR__ . '/../fns/fah_fns.php';
require_once __DIR__ . '/../queries/folding/folding_insert.php';
require_once __DIR__ . '/../queries/folding/folding_select_by_user_id.php';
require_once __DIR__ . '/../queries/folding/folding_select_list.php';
require_once __DIR__ . '/../queries/folding/folding_update.php';

// message, insert rank token
require_once __DIR__ . '/../queries/messages/message_insert.php';
require_once __DIR__ . '/../queries/rank_tokens/rank_token_select.php';
require_once __DIR__ . '/../queries/rank_tokens/rank_token_upsert.php';

// remove expired servers
require_once __DIR__ . '/../queries/servers/servers_deactivate_expired.php';
require_once __DIR__ . '/../queries/servers/servers_delete_old.php';

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
