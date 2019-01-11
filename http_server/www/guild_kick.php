<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$target_id = (int) default_post('user_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // check referrer
    require_trusted_ref('kick someone from your guild');

    // rate limiting
    $rl_msg = 'Please wait at least 15 seconds before attempting to kick another player from your guild.';
    rate_limit('guild-kick-attempt-'.$ip, 15, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // gather info
    $user_id = (int) token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);
    $target_account = user_select_expanded($pdo, $target_id);
    $guild = guild_select($pdo, $account->guild);

    // sanity checks
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }
    if ((int) $guild->owner_id !== $user_id) {
        throw new Exception('You are not the owner of this guild.');
    }
    if ($account->power <= 0) {
        $e = 'Guests can\'t kick users from guilds. To access this feature, please create your own account.';
        throw new Exception($e);
    }
    if ($target_account->guild != $account->guild) {
        throw new Exception('This user is not in your guild.');
    }
    if ($user_id == $target_id) {
        throw new Exception('You can\'t kick yourself out of your own guild, silly!');
    }
    if (!isset($target_id)) {
        throw new Exception('Who are you trying to kick from your guild?');
    }

    // edit guild in db
    user_update_guild($pdo, $target_id, 0);
    guild_increment_member($pdo, $guild->guild_id, -1);

    $kicked_name = htmlspecialchars($target_account->name, ENT_QUOTES);
    $guild_name = htmlspecialchars($guild->guild_name, ENT_QUOTES);

    // tell it to the world
    $ret->success = true;
    $ret->message = "$kicked_name has been kicked from $guild_name.";
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
