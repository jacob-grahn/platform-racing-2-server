<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/guild_invitations.php';
require_once QUERIES_DIR . '/servers.php';

$guild_id = (int) default_post('guild_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least 30 seconds before attempting to join another guild.';
    rate_limit('guild-join-attempt-'.$ip, 30, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // gather information
    $user_id = (int) token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);
    $guild = guild_select($pdo, $guild_id);

    // sanity checks
    if ($account->guild != 0) {
        throw new Exception('You are already a member of a guild.');
    }
    if ($account->power <= 0) {
        $e = 'Guests can\'t join guilds. To access this feature, please create your own account.';
        throw new Exception($e);
    }
    if ($guild->member_count >= 200) {
        throw new Exception('This guild is full.');
    }
    if (!guild_invitation_select($pdo, $guild_id, $user_id)) {
        throw new Exception('The invitation has expired.');
    }

    // join the guild
    guild_invitation_delete($pdo, $guild_id, $user_id);
    guild_increment_member($pdo, $guild_id, 1);
    user_update_guild($pdo, $user_id, $guild_id);

    // tell the world
    $ret->success = true;
    $ret->user_id = $user_id;
    $ret->guild_id = (int) $guild->guild_id;
    $ret->guild_name = $guild->guild_name;
    $ret->emblem = $guild->emblem;
    $ret->message = 'Welcome to ' . htmlspecialchars($guild->guild_name, ENT_QUOTES) . '!';
    @poll_servers(servers_select($pdo), 'player_guild_change`' . json_encode($ret), false);
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
