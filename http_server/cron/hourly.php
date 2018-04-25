<?php

// all fns
require_once __DIR__ . '/../fns/all_fns.php';

// ensure part awards
require_once __DIR__ . '/../queries/part_awards/part_awards_select_list.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_delete_old.php';
require_once __DIR__ . '/../queries/part_awards/ensure_awards.php';

// tell the command line
$time = date('r');
output("Hourly CRON starting at $time...");

// connect
$pdo = pdo_connect();

generate_level_list($pdo, 'newest');
generate_level_list($pdo, 'best');
generate_level_list($pdo, 'best_today');
generate_level_list($pdo, 'campaign');
ensure_awards($pdo);

// tell the command line
output('Hourly CRON successful.');
