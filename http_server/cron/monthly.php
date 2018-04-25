<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/best_levels/best_levels_monthly.php';
require_once __DIR__ . '/../queries/messages/messages_delete_old.php';
require_once __DIR__ . '/../queries/misc/all_optimize.php';

// tell the command line
$time = date('r');
output("Monthly CRON starting at $time...");

// connect
$pdo = pdo_connect();

best_levels_monthly($pdo);
messages_delete_old($pdo);
all_optimize($pdo);

// tell the command line
output('Monthly CRON successful.');
