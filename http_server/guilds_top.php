<?php

header("Content-Type: text/plain");

require_once GEN_HTTP_FNS;

$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('guilds-top-'.$ip, 5, 3);

    // connect to the db
    $pdo = pdo_connect();

    // select list from db
    $guilds = guilds_select_by_most_gp_today($pdo);

    // get active member count guild by guild
    foreach ($guilds as $guild) {
        $guild->active_count = guild_count_active($pdo, $guild->guild_id);
    }

    // tell it to the world
    $ret->success = true;
    $ret->guilds = $guilds;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
