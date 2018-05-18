<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';
require_once QUERIES_DIR . '/users/user_update_guild.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_increment_member.php';

$target_id = find('userId');
$ip = get_ip();

try {
    // check referrer
    require_trusted_ref('kick someone from your guild');

    // rate limiting
    rate_limit(
        'guild-kick-attempt-'.$ip,
        15,
        1,
        'Please wait at least 15 seconds before attempting to kick another player from your guild.'
    );

    // connect
    $pdo = pdo_connect();

    // gather info
    $user_id = token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);
    $target_account = user_select_expanded($pdo, $target_id);
    $guild = guild_select($pdo, $account->guild);

    // sanity checks
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }
    if ($guild->owner_id != $user_id) {
        throw new Exception('You are not the owner of this guild.');
    }
    if ($account->power <= 0) {
        throw new Exception(
            "Guests can't kick users from guilds. ".
            "To access this feature, please create your own account."
        );
    }
    if ($target_account->guild != $account->guild) {
        throw new Exception('They are not in your guild.');
    }
    if ($user_id == $target_id) {
        throw new Exception('Do not kick your self, yo.');
    }
    if (!isset($target_id)) {
        throw new Exception('Who are you trying to kick from your guild?');
    }

    // edit guild in db
    user_update_guild($pdo, $target_id, 0);
    guild_increment_member($pdo, $guild->guild_id, -1);

    // tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = htmlspecialchars($target_account->name)
        .' has been kicked from '.htmlspecialchars($guild->guild_name).'.';
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
} finally {
    echo json_encode($reply);
    die();
}
