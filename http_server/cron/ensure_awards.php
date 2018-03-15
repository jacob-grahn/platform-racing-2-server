<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_select_list.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_delete_old.php';

try {

    // connect
    $pdo = pdo_connect();

    // select all records, they get cleared out weekly or somesuch
    $awards = part_awards_select_list($pdo);

    // give users their awards
    foreach ($awards as $row) {
        if ($row->part == 0) {
            $part = '*';
        } else {
            $part = $row->part;
        }
        $type = $row->type;
        $user_id = $row->user_id;
        try {
            award_part($pdo, $user_id, $type, $part, false);
            echo "user_id: $user_id, type: $type, part: $part \n";
        } catch (Exception $e) {
            echo "Error: $e";
        }
    }

    // delete older records
    part_awards_delete_old($pdo);

} catch (Exception $e) {
    echo "Error: $e";
    exit();
}
