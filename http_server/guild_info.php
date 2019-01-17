<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$guild_id = (int) default_get('id', 0);
$guild_name = default_get('name', '');
$get_members = default_get('getMembers', 'no');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('guild-info-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // sanity check: was any information requested?
    if ($guild_id <= 0 && is_empty($guild_name)) {
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
    if (substr($guild->emblem, -2) === '.j') {
        $guild->emblem = str_replace('.j', '.jpg', $guild->emblem);
    }

    // members
    $members = $get_members === 'yes' ? guild_select_members($pdo, $guild_id) : array();
    $guild->active_count = guild_count_active($pdo, $guild->guild_id);

    // tell it to the world
    $ret->success = true;
    $ret->guild = $guild;
    $ret->members = $members;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
