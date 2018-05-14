<?php

header("Content-Type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pr2/pr2_fns.php';
require_once QUERIES_DIR . '/guilds/guilds_select_by_most_gp_today.php';

$sort = find('sort', 'gpToday');
$ip = get_ip();

try {
    // rate limiting
    rate_limit('guilds-top-'.$ip, 5, 3);

    //--- connect to the db
    $pdo = pdo_connect();


    //--- sanity check
    $allowed_sort_values = array( 'gpToday', 'gpTotal', 'members', 'activeMembers' );
    if (array_search($sort, $allowed_sort_values) === false) {
        throw new Exception('Unexpected sort value');
    }


    //--- select list from db
    $guilds = guilds_select_by_most_gp_today($pdo);


    //--- get active member count guild by guild
    //--- also disable html parsing
    foreach ($guilds as $guild) {
        $guild->active_count = guild_count_active($pdo, $guild->guild_id);
        $guild->guild_name = htmlspecialchars($guild->guild_name);
    }


    //--- tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->guilds = $guilds;
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
