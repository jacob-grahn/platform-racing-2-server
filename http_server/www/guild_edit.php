<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_update.php';

$note = filter_swears(find('note'));
$guild_name = filter_swears(find('name'));
$emblem = filter_swears(find('emblem'));
$ip = get_ip();

try {
    // get and validate referrer
    require_trusted_ref('edit your guild');

    // rate limiting
    rate_limit(
        'guild-edit-attempt-'.$ip,
        10,
        3,
        "Please wait at least 10 seconds before editing your guild again."
    );

    // connect to the db
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit(
        'guild-edit-attempt-'.$user_id,
        10,
        3,
        "Please wait at least 10 seconds before editing your guild again."
    );

    // get account and guild info
    $account = user_select_expanded($pdo, $user_id);
    $guild = guild_select($pdo, $account->guild);

    // sanity checks
    if ($account->power <= 0) {
        throw new Exception(
            "Guests can't edit guilds. ".
            "To access this feature, please create your own account."
        );
    }
    if ($account->guild == 0) {
        throw new Exception('You are not a member of a guild.');
    }
    if ($guild->owner_id != $user_id) {
        throw new Exception('You are not the owner of this guild.');
    }
    if (!isset($note)) {
        throw new Exception('Your guild needs a prose.');
    }
    if (!isset($guild_name)) {
        throw new Exception('Your guild needs a name.');
    }
    if (!isset($emblem)) {
        throw new Exception('Your guild needs an emblem.');
    }
    if (preg_match('/.jpg$/', $emblem) !== 1
        || preg_match('/\.\.\//', $emblem) === 1
        || preg_match('/\?/', $emblem) === 1
    ) {
        throw new Exception('Emblem invalid');
    }
    if (preg_match("/^[a-zA-Z0-9\s-]+$/", $guild_name) !== 1) {
        throw new Exception('Guild name is invalid. You may only use alphanumeric characters, spaces and hyphens.');
    }
    if (strlen(trim($guild_name)) === 0) {
        throw new Exception('I\'m not sure what would happen if you didn\'t
            enter a guild name, but it would probably destroy the world.');
    }

    // edit guild in db
    guild_update($pdo, $guild->guild_id, $guild_name, $emblem, $note, $guild->owner_id);

    // tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->message = 'Guild edited successfully!';
    $reply->guildId = $guild->guild_id;
    $reply->emblem = $emblem;
    $reply->guildName = $guild_name;
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
} finally {
    echo json_encode($reply);
    die();
}
