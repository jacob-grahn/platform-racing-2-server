<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/messages.php';
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

    // log and send update if a mod
    if ($log_action === true) {
        // mod action log
        $str = "$account->name edited guild #$guild->guild_id from $ip";
        $changes_arr = array();
        if ($guild_name !== $guild->guild_name || $note !== $guild->note || $guild->emblem !== $emblem) {
            $punc = false;
            $str .= ' {';
            if ($guild_name !== $guild->guild_name) {
                array_push($changes_arr, 'Name (old: ' . htmlspecialchars($guild->guild_name, ENT_QUOTES) . ')');
                $str .= "old_name: $guild->guild_name, new_name: $guild_name";
                $punc = true;
            }
            if ($note !== $guild->note) {
                array_push($changes_arr, 'Prose (old: ' . htmlspecialchars($guild->note, ENT_QUOTES) . ')');
                $str = $str . ($punc === true ? '; ' : '') . "old_note: $guild->note, new_note: $note";
                $punc = true;
            }
            if ($emblem !== $guild->emblem) {
                array_push($changes_arr, "Emblem (contact for old)");
                $str = $str . ($prev === true ? '; ' : '') . "old_emblem: $guild->emblem, new_emblem: $emblem";
            }
            $str .= '}';
        }
        mod_action_insert($pdo, $account->user_id, $str, 0, $ip);

        // send the guild owner a PM
        $owner_name = id_to_name($pdo, $guild->owner_id);
        $owner_name = htmlspecialchars($owner_name, ENT_QUOTES);
        $pm = "Dear $owner_name,\n\n"
            ."This is an automatic message generated to let you know that I have edited your guild.\n\n"
            ."What Changed:\n - "
            .join("\n - ", $changes_arr)
            ."\n\nIf you have any questions, please contact me or another member of the PR2 staff team.\n\n"
            ."All the best,\n"
            .htmlspecialchars($account->name, ENT_QUOTES);
        message_insert($pdo, $guild->owner_id, $account->user_id, $pm, 0);
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
