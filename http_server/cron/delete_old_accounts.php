<?php

set_time_limit(0);

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/users/users_select_old.php';
require_once __DIR__ . '/../queries/users/user_select_level_plays.php';
require_once __DIR__ . '/../queries/users/user_delete.php';

$pdo = pdo_connect();
$min_time = time() - (60 * 60 * 24 * 30 * 12 * 3); //three years

$users = users_select_old($pdo);
$num_users = number_format(count($users));

output("$num_users accounts have not been logged into recently.");

foreach ($users as $row) {
    $user_id = $row->user_id;
    $rank = $row->rank;

    $play_count = user_select_level_plays($pdo, $user_id);

    $str = "$user_id has $play_count level plays and is rank $rank.";
    if ($play_count > 100 || $rank > 15) {
        output("$str Spared!");
    } else {
        output("$str DELETING...");
        user_delete($pdo, $user_id);
        output("$user_id was successfully deleted.");
    }
}
