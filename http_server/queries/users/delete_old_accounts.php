<?php

function delete_old_accounts($pdo)
{
    set_time_limit(0);

    // get data
    $users = users_select_old($pdo);
    
    // tell the world
    $num_users = number_format(count($users));
    output("$num_users accounts meet the time criteria for deletion.");

    // count
    $spared = 0;
    $deleted = 0;
    
    // delete or spare
    foreach ($users as $row) {
        $user_id = $row->user_id;
        $rank = $row->rank;

        $play_count = user_select_level_plays($pdo, $user_id);

        $str = "$user_id has $play_count level plays and is rank $rank.";
        if ($play_count > 100 || $rank > 15) {
            output("$str Spared!");
            $spared++;
        } else {
            output("$str DELETING...");
            user_delete($pdo, $user_id);
            output("$user_id was successfully deleted.");
            $deleted++;
        }
    }
    
    // tell the world
    output("Old account deletion completed. Stats:\n".
        "Spared: $spared / $num_users\n".
        "Deleted: $deleted / $num_users");
}
