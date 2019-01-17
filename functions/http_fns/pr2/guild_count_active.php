<?php

function guild_count_active($pdo, $guild_id)
{
    $key = 'ga' . $guild_id;

    if (apcu_exists($key)) {
        $active_count = apcu_fetch($key);
    } else {
        $active_count = guild_select_active_member_count($pdo, $guild_id);
        apcu_store($key, $active_count, 3600); // one hour
    }
    return( $active_count );
}
