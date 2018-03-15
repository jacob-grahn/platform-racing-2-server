<?php

require_once __DIR__ . '/../fns/all_fns.php';

header("Content-type: text/plain");

$guild_id = (int) find_no_cookie('guildId', 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('guild-join-attempt-'.$ip, 30, 1, 'Please wait at least 30 seconds before trying to join this guild again.');

    // connect
    $db = new DB();
    $pdo = pdo_connect();

    // gather information
    $user_id = token_login($pdo, false);
    $account = $db->grab_row('user_select_expanded', array($user_id));
    $guild = $db->grab_row('guild_select', array($guild_id), 'Could not find a guild with that ID.');

    // sanity checks
    if ($account->guild != 0) {
        throw new Exception('You are already a member of a guild.');
    }
    if ($account->power <= 0) {
                throw new Exception('Guests can\'t join guilds.');
    }
    if ($guild->member_count >= 200) {
        throw new Exception('This guild is full.');
    }
    $db->grab_row('guild_invitation_select', array($guild_id, $user_id), 'This invitation has expired.');

    // join the guild
    $db->call('guild_invitation_delete', array($guild_id, $user_id));
    $db->call('guild_increment_member', array($guild_id, 1));
    $db->call('user_update_guild', array($user_id, $guild_id));

    // tell the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = 'Welcome to '.$guild->guild_name.'!';
    $reply->guildId = $guild->guild_id;
    $reply->guildName = $guild->guild_name;
    $reply->emblem = $guild->emblem;
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
