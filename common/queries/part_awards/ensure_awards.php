<?php

function ensure_awards($pdo)
{
    // select all records
    $awards = part_awards_select_list($pdo);

    // give users their awards
    foreach ($awards as $row) {
        $part = $row->part == 0 ? '*' : $row->part;
        $type = $row->type;
        $user_id = $row->user_id;
        try {
            award_part($pdo, $user_id, $type, $part, false);
            echo "user_id: $user_id, type: $type, part: $part \n";
        } catch (Exception $e) {
            echo "Error: $e";
        }
    }

    // delete records older than a week
    part_awards_delete_old($pdo);
}
