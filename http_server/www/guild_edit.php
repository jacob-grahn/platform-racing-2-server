<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';
require_once QUERIES_DIR . '/users/user_select_mod.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_update.php';
require_once QUERIES_DIR . '/staff/actions/mod_action_insert.php';

$guild_id = (int) find('guild_id');
$note = filter_swears(find('note'));
$guild_name = filter_swears(find('name'));
$emblem = filter_swears(find('emblem'));
$ip = get_ip();
$log_action = false;

try {
    // get and validate referrer
    require_trusted_ref('edit your guild');

    // rate limiting
    $rl_msg = 'Please wait at least 10 seconds before editing your guild again.';
    rate_limit('guild-edit-attempt-'.$ip, 10, 3, $rl_msg);

    // connect to the db
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('guild-edit-attempt-'.$user_id, 10, 3, $rl_msg);

    // get account and guild info
    $account = user_select_expanded($pdo, $user_id);
    $guild = guild_select($pdo, $guild_id);

    // sanity checks
    if ($account->power <= 0) {
        $e_msg = 'Guests can\'t edit guilds. To access this feature, please create your own account.';
        throw new Exception($e_msg);
    }
    if ($account->guild == 0 && $account->power < 2) {
        throw new Exception('You are not a member of a guild.');
    }
    if ($guild->owner_id != $user_id) {
        if ($account->power < 2) {
            throw new Exception('You are not the owner of this guild.');
        } else {
            $mod = user_select_mod($pdo, $user_id, true);
            $can_unpub = (int) $mod->can_unpublish_level;
            if ($can_unpub === 0) {
                throw new Exception("You lack the power to edit this guild.");
            }
            $log_action = true;
        }
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
        throw new Exception('Your emblem is invalid.');
    }
    if (preg_match("/^[a-zA-Z0-9\s-]+$/", $guild_name) !== 1) {
        $e_msg = 'Your guild name is invalid. You may only use alphanumeric characters, spaces and hyphens.';
        throw new Exception($e_msg);
    }
    if (strlen(trim($guild_name)) === 0) {
        throw new Exception('Your guild needs a name.');
    }

    // edit guild in db
    guild_update($pdo, $guild->guild_id, $guild_name, $emblem, $note, $guild->owner_id);

    // log update if a mod
    if ($log_action === true) {
        $str = "$account->name edited guild #$guild->guild_id from $ip";
        if ($guild_name !== $guild->guild_name || $note !== $guild->note || $guild->emblem !== $emblem) {
            $changes = false;
            $str .= ' {';
            if ($guild_name !== $guild->guild_name) {
                $str .= "old_name: $guild->guild_name, new_name: $guild_name";
                $changes = true;
            }
            if ($note !== $guild->note) {
                $str = $str . ($changes === true ? '; ' : '');
                $str .= "old_note: $guild->note, new_note: $note";
                $changes = true;
            }
            if ($emblem !== $guild->emblem) {
                $str = $str . ($changes === true ? '; ' : '');
                $str .= "old_emblem: $guild->emblem, new_emblem: $emblem";
                $changes = true;
            }
            $str .= '}';
        }
        mod_action_insert($pdo, $account->user_id, $str, 0, $ip);
    }

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
}
