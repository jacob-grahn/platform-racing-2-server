<?php

// all fns
require_once __DIR__ . '/../fns/all_fns.php';

// atb reset
require_once __DIR__ . '/../queries/best_levels/best_levels_reset.php';

// delete old messages
require_once __DIR__ . '/../queries/messages/messages_delete_old.php';

// optimize tables
require_once __DIR__ . '/../queries/misc/all_optimize.php';

// delete old accounts
require_once __DIR__ . '/../queries/users/delete_old_accounts.php';
require_once __DIR__ . '/../queries/users/users_select_old.php';
require_once __DIR__ . '/../queries/users/user_select_level_plays.php';
require_once __DIR__ . '/../queries/users/user_delete.php';

// tell the command line
$time = date('r');
output("Monthly CRON starting at $time...");

// connect
$pdo = pdo_connect();

best_levels_reset($pdo);
messages_delete_old($pdo);
all_optimize($pdo);
delete_old_accounts($pdo);

// tell the command line
output('Monthly CRON successful.');
