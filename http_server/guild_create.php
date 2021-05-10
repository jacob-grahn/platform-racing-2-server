<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/servers.php';

$note = filter_swears(default_post('note', ''));
$guild_name = filter_swears(default_post('name', ''));
$emblem = default_post('emblem', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // get and validate referrer
    require_trusted_ref('create a guild');

    // rate limiting
    $rl_msg = 'Please wait at least 10 seconds before attempting to create a guild again.';
    rate_limit('guild-create-attempt-'.$ip, 10, 3, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('guild-create-attempt-'.$user_id, 10, 3, $rl_msg);

    // get user info
    $account = user_select_expanded($pdo, $user_id);

    // sanity checks
    if ($account->rank < 20) {
        throw new Exception('You must be rank 20 or above to create a guild.');
    }
    if ($account->power <= 0) {
        throw new Exception(
            "Guests can't create guilds. ".
            "To access this feature, please create your own account."
        );
    }
    if ($account->guild != 0) {
        throw new Exception('You are already a member of a guild.');
    }
    if (!isset($note)) {
        throw new Exception('You need a guild prose.');
    }
    if (!isset($guild_name)) {
        throw new Exception('Your guild needs a name.');
    }
    if (!isset($emblem)) {
        throw new Exception('Your guild needs an emblem.');
    }
    if (preg_match('/^[0-9]+\-[0-9]+\.jpg$/i', $emblem) and (preg_match('/^default\-emblem\.jpg$/i', $emblem) !== 1 {
        throw new Exception('Guild emblem is invalid.');
    }
    if (preg_match("/^[a-zA-Z0-9\s-]+$/", $guild_name) !== 1) {
        throw new Exception('Guild name is invalid. You may only use alphanumeric characters, spaces, and hyphens.');
    }
    if (strlen(trim($guild_name)) === 0) {
        $e = 'I\'m not sure, but I think not entering a guild name would probably destroy the world...';
        throw new Exception($e);
    }

    // check if guild exists
    if (guild_name_to_id($pdo, $guild_name, true) !== false) {
        throw new Exception('A guild with this name already exists. Please choose a new name.');
    }

    // more rate limiting
    rate_limit('guild-create-'.$ip, 3600, 1, 'You may only create one guild per hour. Try again later.');
    rate_limit('guild-create-'.$user_id, 3600, 1, 'You may only create one guild per hour. Try again later.');

    // add guild to db
    $guild_id = guild_insert($pdo, $user_id, $guild_name, $emblem, $note);
    user_update_guild($pdo, $user_id, $guild_id);

    // tell it to the world
    $ret->success = true;
    $ret->create = true;
    $ret->message = 'Congratulations on starting your own guild! What an auspicious day!';
    $ret->user_id = $user_id;
    $ret->guild_id = $guild_id;
    $ret->guild_name = $guild_name;
    $ret->emblem = $emblem;
    @poll_servers(servers_select($pdo), 'player_guild_change`' . json_encode($ret), false);
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
