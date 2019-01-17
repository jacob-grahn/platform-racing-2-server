<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/mod_actions.php';

$guild_id = (int) default_post('guild_id', 0);
$note = filter_swears(default_post('note', ''));
$guild_name = filter_swears(default_post('name', ''));
$emblem = filter_swears(default_post('emblem', ''));
$ip = get_ip();
$log_action = false;

$ret = new stdClass();
$ret->success = false;

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

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
        $e = 'Guests can\'t edit guilds. To access this feature, please create your own account.';
        throw new Exception($e);
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
                throw new Exception('You lack the power to edit this guild.');
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
        $e = 'Your guild name is invalid. You may only use alphanumeric characters, spaces and hyphens.';
        throw new Exception($e);
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
    $ret->success = true;
    $ret->message = 'Guild edited successfully!';
    $ret->guildId = $guild->guild_id;
    $ret->emblem = $emblem;
    $ret->guildName = $guild_name;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
