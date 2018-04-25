<?php

set_time_limit(0);

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/users/users_select_old.php';
require_once __DIR__ . '/../queries/users/user_select_level_plays.php';
require_once __DIR__ . '/../queries/users/user_delete.php';

$pdo = pdo_connect();
$min_time = time() - (60 * 60 * 24 * 30 * 12 * 3); //three years

$users = users_select_old($pdo);

echo(number_format(count($users)) . " accounts have not been logged into recently. \n");

foreach ($users as $row) {
    $user_id = $row->user_id;
    $rank = $row->rank;

    $play_count = user_select_level_plays($pdo, $user_id);

    $str = "$user_id has $play_count level plays and is rank $rank.";
    if ($play_count > 100 || $rank > 15) {
        output("$str SPARE \n");
    } else {
        output("$str DELETE \n");
        user_delete($pdo, $user_id);
    }
}
