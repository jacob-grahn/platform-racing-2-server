<?php

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/all_optimize.php';
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/best_levels.php';
require_once QUERIES_DIR . '/level_backups.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/new_levels.php';
require_once QUERIES_DIR . '/servers.php';

// tell the command line
$time = date('r');
output("Weekly CRON starting at $time...");

// connect
$pdo = pdo_connect();

level_backups_delete_old($pdo);
new_levels_delete_old($pdo);
messages_delete_old($pdo);
bans_delete_old($pdo);
users_reset_status($pdo);
best_levels_reset($pdo);
restart_servers($pdo);
all_optimize($pdo, $DB_NAME);

// deletes old accounts every four weeks
if (date('W') % 4 === 0) {
    require_once FNS_DIR . '/cron/cron_fns.php';
    require_once QUERIES_DIR . '/user_delete.php';
    delete_old_accounts($pdo);
}

// tell the command line
output('Weekly CRON successful.');
