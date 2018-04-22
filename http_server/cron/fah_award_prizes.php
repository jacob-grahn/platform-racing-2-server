<?php

// get general functions
require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/output_fns.php';

// select stats from fah db
require_once __DIR__ . '/../queries/fah/stats/stats_select_all.php';
require_once __DIR__ . '/../queries/fah/stats/stats_select_by_name.php';
require_once __DIR__ . '/../queries/fah/stats/stats_insert.php';

// folding_at_home data select/insert/update from/into/in pr2 db
require_once __DIR__ . '/../queries/folding/folding_insert.php';
require_once __DIR__ . '/../queries/folding/folding_select_by_user_id.php';
require_once __DIR__ . '/../queries/folding/folding_select_list.php';
require_once __DIR__ . '/../queries/folding/folding_update.php';
require_once __DIR__ . '/../cron/fah_award_prizes_fns.php';

// message, insert rank token
require_once __DIR__ . '/../queries/messages/message_insert.php';
require_once __DIR__ . '/../queries/rank_tokens/rank_token_select.php';
require_once __DIR__ . '/../queries/rank_tokens/rank_token_upsert.php';

$prize_array = array();
$processed_names = array();


// connect to the db
$fah_pdo = pdo_fah_connect();
$pdo = pdo_connect();


// create a list of existing users and their prizes
$folding_rows = folding_select_list($pdo);
foreach ($folding_rows as $row) {
    $prize_array[strtolower($row->name)] = $row;
}


// get fah user stats
$stats = stats_select_all($fah_pdo);
foreach ($stats as $user) {
    add_prizes($pdo, $user->fah_name, $user->points, $prize_array, $processed_names);
}
