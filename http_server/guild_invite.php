<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/pr2/pr2_fns.php';
require_once QUERIES_DIR . '/guild_invitations.php';

$target_id = (int) default_post('user_id', 0);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least 5 seconds before attempting to invite another player to your guild.';
    rate_limit('guild-invite-attempt-'.$ip, 5, 2, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // gather info
    $user_id = (int) token_login($pdo, false);
    $account = user_select_expanded($pdo, $user_id);
    $guild = guild_select($pdo, $account->guild);
    $target_account = user_select_expanded($pdo, $target_id);

    // sanity checks
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }
    if ($guild->owner_id != $user_id) {
        throw new Exception('You are not the owner of this guild.');
    }
    if ($target_account->guild != 0) {
        throw new Exception('They are already in a guild.');
    }
    if ($account->power <= 0 || $target_account->power <= 0) {
        $e = 'Guests can\'t join or invite users to guilds. To access this feature, please create your own account.';
        throw new Exception($e);
    }
    if ($user_id === $target_id) {
        throw new Exception('You can\'t invite yourself to your own guild, silly!');
    }
    if (is_empty($target_id, false)) {
        throw new Exception('Who are you trying to invite?');
    }

    // rate limiting
    $rl_msg = 'You can only invite up to 10 players to your guild per hour. Try again later.';
    rate_limit('guild-invite-'.$ip, 3600, 10, $rl_msg);
    rate_limit('guild-invite-'.$user_id, 3600, 10, $rl_msg);

    // compose an eloquent invitation
    $pm_safe_guild_name = preg_replace("/[^a-zA-Z0-9 ]/", "_", $guild->guild_name);
    $message = "Hi $target_account->name! "
        ."You've been invited to join our guild, [guildlink=$guild->guild_id]" . $pm_safe_guild_name . "[/guildlink]. "
        ."Click [invitelink=$guild->guild_id]here[/invitelink] to accept!";

    // add the invitation to the db
    send_pm($pdo, $user_id, $target_id, $message);
    guild_invitation_insert($pdo, $guild->guild_id, $target_id);

    // tell it to the world
    $ret->success = true;
    $ret->message = 'Your invitation has been sent!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
