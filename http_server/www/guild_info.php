<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pr2/pr2_fns.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_select_by_name.php';
require_once QUERIES_DIR . '/guilds/guild_select_members.php';

$guild_id = find_no_cookie('id', 0);
$guild_name = find_no_cookie('name', '');
$get_members = find_no_cookie('getMembers', 'no');
$ip = get_ip();

try {
    // rate limiting
    rate_limit('guild-info-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // sanity check: was any information requested?
    if ((!is_numeric($guild_id) || $guild_id <= 0) && $guild_name == '') {
        throw new Exception('No guild name or ID was provided.');
    }

    // get guild infos
    if ($guild_id > 0) {
        $guild = guild_select($pdo, $guild_id);
    } else {
        $guild = guild_select_by_name($pdo, $guild_name);
        $guild_id = $guild->guild_id;
    }

    // check for .j instead of .jpg on the end of the emblem file name
    if (substr($guild->emblem, -2) == '.j') {
        $guild->emblem = str_replace('.j', '.jpg', $guild->emblem);
    }

    // get members
    $members = array();
    if ($get_members == 'yes') {
        $members = guild_select_members($pdo, $guild_id);
    }

    // count active members
    $guild->active_count = guild_count_active($pdo, $guild->guild_id);

    // tell it to the world
    $reply = new stdClass();
    $reply->guild = $guild;
    $reply->members = $members;
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
} finally {
    echo json_encode($reply);
    die();
}
