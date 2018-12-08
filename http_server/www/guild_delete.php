<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/guilds/guild_select.php'; // select a guild
require_once QUERIES_DIR . '/guilds/guild_delete.php'; // delete a guild
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php'; // record the mod action

$guild_id = (int) find_no_cookie('guild_id');
$ip = get_ip();
$reply = new stdClass();

try {
    // rate limiting
    rate_limit('guild-delete-'.$ip, 5, 2);

    // connect to the db
    $pdo = pdo_connect();

    // check their login and make some rad variables
    $admin = check_moderator($pdo, true, 3);
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;

    // more rate limiting
    rate_limit('guild-delete-'.$admin_id, 5, 2);

    // check if the guild exists and make some rad variables
    $guild = guild_select($pdo, $guild_id);
    $name = $guild->guild_name;
    $note = $guild->note;
    $emblem = $guild->emblem;
    $owner = $guild->owner_id;

    // edit guild in db
    guild_delete($pdo, $guild_id);

    // record the deletion in the action log
    $str = "$admin_name deleted guild #$guild_id from $ip {name: $name, note: $note, emblem: $emblem, owner: $owner}";
    admin_action_insert($pdo, $admin_id, $str, 0, $ip);

    // safety first
    $safe_guild_name = htmlspecialchars($name);

    // tell the world
    $reply->success = true;
    $reply->message = "\"$safe_guild_name\" (ID #$guild_id) was successfully deleted.";
} catch (Exception $e) {
    $reply->error = $e->getMessage();
} finally {
    die(json_encode($reply));
}
