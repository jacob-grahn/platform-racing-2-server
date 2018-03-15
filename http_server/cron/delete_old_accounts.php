<?php

set_time_limit(0);

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/users/users_select_old.php';
require_once __DIR__ . '/../queries/users/user_select_level_plays.php';
require_once __DIR__ . '/../queries/users/user_delete.php';

$pdo = pdo_connect();
$min_time = time() - (60 * 60 * 24 * 30 * 12 * 3); //three years

$users = users_select_old($pdo, $min_time);

output(number_format(count($users)) . ' accounts have not been logged into recently.');

foreach ($users as $row) {
    $user_id = $row->user_id;
    $rank = $row->rank;

    $play_count = user_select_level_plays($pdo, $user_id);

    $str = "$user_id plays: $play_count rank: $rank.";
    if ($play_count > 100 || $rank > 15) {
        output("$str SPARE");
    } else {
        output("$str DELETE");
        user_delete($pdo, $user_id);
    }
}


function output($str)
{
    echo "$str\n";
}
