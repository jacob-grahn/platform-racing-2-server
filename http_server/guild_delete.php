<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/servers.php';

$guild_id = (int) default_post('guild_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('guild-delete-'.$ip, 5, 2);

    // connect to the db
    $pdo = pdo_connect();

    // check their login and make some rad variables
    $admin = check_moderator($pdo, null, true, 3);
    $admin_id = (int) $admin->user_id;

    // more rate limiting
    rate_limit('guild-delete-'.$admin_id, 5, 2);

    // check if the guild exists and make some rad variables
    $guild = guild_select($pdo, $guild_id);
    $name = $guild->guild_name;
    $note = $guild->note;
    $emblem = $guild->emblem;
    $owner = (int) $guild->owner_id;

    // edit guild in db
    guild_delete($pdo, $guild_id);

    // record the deletion in the action log
    $str = "$admin->name deleted guild #$guild_id from $ip {name: $name, note: $note, emblem: $emblem, owner: $owner}";
    admin_action_insert($pdo, $admin_id, $str, 'guild-delete', $ip);

    // tell the world
    $safe_guild_name = htmlspecialchars($name, ENT_QUOTES);
    $ret->success = true;
    $ret->delete = true;
    $ret->guild_id = $guild_id;
    $ret->changer_id = $admin_id;
    $ret->message = "\"$safe_guild_name\" (ID #$guild_id) was successfully deleted.";
    @poll_servers(servers_select($pdo), 'guild_change`' . json_encode($ret), false);
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
