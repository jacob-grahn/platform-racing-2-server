<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/servers.php';

$token = find('token', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // get and validate referrer
    require_trusted_ref('leave your guild');

    // rate limiting
    rate_limit('guild-leave-attempt-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // get their login
    $user_id = (int) token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);

    // sanity: are they in a guild?
    if ((int) $account->guild === 0) {
        throw new Exception('You are not a member of a guild.');
    }

    // sanity: are they the guild owner?
    $owner_id = (int) guild_select_owner_id($pdo, $account->guild, true);
    if ($owner_id === $user_id) {
        $msg = 'You own this guild. Before leaving, you must '
            . urlify('https://pr2hub.com/guild_transfer.php', 'transfer guild ownership') . '.';
        throw new Exception($msg);
    }

    // leave the guild
    guild_increment_member($pdo, $account->guild, -1);
    user_update_guild($pdo, $user_id, 0);

    // tell it to the world
    $ret->success = true;
    $ret->message = 'You have left the guild.';
    $ret->user_id = $user_id;
    $ret->guild_id = 0;
    $ret->guild_name = '';
    @poll_servers(servers_select($pdo), 'player_guild_change`' . json_encode($ret), false);
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
