<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';

$guild_id = find_no_cookie('guild_id');
$ip = get_ip();

try {
    // rate limiting
    rate_limit('guild-delete-'.$ip, 5, 2);

    // connect to the db
    $db = new DB();

    // check their login and make some rad variables
    $mod = check_moderator($db);
    $mod_name = $mod->name;
    $mod_id = $mod->user_id;
    $ip = $mod->ip;

    // more rate limiting
    rate_limit('guild-delete-'.$mod_id, 5, 2);

    // check if the guild exists and make some rad variables
    $guild = $db->grab_row('guild_select', array($guild_id), 'Could not find a guild with that id.');
    $guild_name = $guild->guild_name;
    $guild_note = $guild->note;
    $guild_owner = $guild->owner_id;

    // edit guild in db
    $db->call('guild_delete', array($guild_id), 'Could not delete the guild.');

    // record the deletion in the action log
    $db->call('mod_action_insert', array($mod_id, "$mod_name deleted guild $guild_id from $ip {guild_name: $guild_name, guild_prose: $guild_note, owner_id: $guild_owner}", $mod_id, $ip));


    // tell the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = 'Guild deleted.';
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
