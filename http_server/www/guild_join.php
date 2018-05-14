<?php

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_increment_member.php';
require_once QUERIES_DIR . '/guild_invitations/guild_invitation_select.php';
require_once QUERIES_DIR . '/guild_invitations/guild_invitation_delete.php';
require_once QUERIES_DIR . '/users/user_update_guild.php';

header("Content-type: text/plain");

$guild_id = (int) find_no_cookie('guildId', 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit(
        'guild-join-attempt-'.$ip,
        30,
        1,
        'Please wait at least 30 seconds before trying to join this guild again.'
    );

    // connect
    $pdo = pdo_connect();

    // gather information
    $user_id = token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);
    $guild = guild_select($pdo, $guild_id);

    // sanity checks
    if ($account->guild != 0) {
        throw new Exception('You are already a member of a guild.');
    }
    if ($account->power <= 0) {
        throw new Exception(
            "Guests can't join guilds. ".
            "To access this feature, please create your own account."
        );
    }
    if ($guild->member_count >= 200) {
        throw new Exception('This guild is full.');
    }

    $invite = guild_invitation_select($pdo, $guild_id, $user_id);
    if (!$invite) {
        throw new Exception('The invitation has expired.');
    }

    // join the guild
    guild_invitation_delete($pdo, $guild_id, $user_id);
    guild_increment_member($pdo, $guild_id, 1);
    user_update_guild($pdo, $user_id, $guild_id);

    // tell the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = 'Welcome to '.$guild->guild_name.'!';
    $reply->guildId = $guild->guild_id;
    $reply->guildName = $guild->guild_name;
    $reply->emblem = $guild->emblem;
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
} finally {
    echo json_encode($reply);
    die();
}
