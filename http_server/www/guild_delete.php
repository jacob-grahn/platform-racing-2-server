<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/guilds/guild_delete.php';
require_once __DIR__ . '/../queries/guilds/guild_select.php';
require_once __DIR__ . '/../queries/staff/actions/mod_action_insert.php';

header("Content-type: text/plain");

$guild_id = find_no_cookie('guild_id');
$ip = get_ip();

try {
    // rate limiting
    rate_limit('guild-delete-'.$ip, 5, 2);

    // connect to the db
    $pdo = pdo_connect();

    // check their login and make some rad variables
    $mod = check_moderator($pdo);
    $mod_name = $mod->name;
    $mod_id = $mod->user_id;
    $ip = $mod->ip;

    // more rate limiting
    rate_limit('guild-delete-'.$mod_id, 5, 2);

    // check if the guild exists and make some rad variables
    $guild = guild_select($pdo, $guild_id);
    $guild_name = $guild->guild_name;
    $guild_note = $guild->note;
    $guild_owner = $guild->owner_id;

    // edit guild in db
    guild_delete($pdo, $guild_id);

    // record the deletion in the action log
    mod_action_insert($pdo, $mod_id, "$mod_name deleted guild $guild_id from $ip (guild_name: $guild_name, guild_prose: $guild_note, owner_id: $guild_owner)", $mod_id, $ip);

    // safety first
    $safe_guild_name = htmlspecialchars($guild_name);
    
    // tell the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = "\"$safe_guild_name\" (ID #$guild_id) was successfully deleted.";
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
