<?php

// all fns
require_once HTTP_FNS . '/all_fns.php';

// delete old...
require_once QUERIES_DIR . '/level_backups/level_backups_delete_old.php'; // level backups
require_once QUERIES_DIR . '/new_levels/new_levels_delete_old.php'; // newest levels
require_once QUERIES_DIR . '/messages/messages_delete_old.php'; // messages
require_once QUERIES_DIR . '/bans/bans_delete_old.php'; // expired/old bans

// reset statuses (users who are offline should appear offline)
require_once QUERIES_DIR . '/users/users_reset_status.php';

// atb reset
require_once QUERIES_DIR . '/best_levels/best_levels_reset.php';

// restart the servers
require_once QUERIES_DIR . '/servers/servers_select.php';
require_once QUERIES_DIR . '/servers/servers_restart_all.php';

// optimize tables
require_once QUERIES_DIR . '/misc/all_optimize.php';

// delete old accounts
require_once QUERIES_DIR . '/users/delete_old_accounts.php';
require_once QUERIES_DIR . '/users/users_select_old.php';
require_once QUERIES_DIR . '/users/user_select_level_plays.php';
require_once QUERIES_DIR . '/users/user_delete.php';

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
delete_old_accounts($pdo);

// tell the command line
output('Weekly CRON successful.');
