<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/friends.php';
require_once QUERIES_DIR . '/ignored.php';

$ip = get_ip();

$target_name = default_post('target_name', '');
$list = default_post('list', '');
$mode = default_post('mode', '');

$allowed_lists = ['friends', 'ignored'];
$allowed_modes = ['add', 'remove'];

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('user-list-'.$ip, 3, 3);

    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity check: was a name sent?
    if (is_empty($target_name)) {
        throw new Exception('A name wasn\'t received.');
    }

    // sanity check: modifying a valid list?
    if (!in_array($list, $allowed_lists)) {
        throw new Exception('Invalid list specified.');
    }

    // sanity check: valid mode?
    if (!in_array($mode, $allowed_modes)) {
        throw new Exception('Invalid mode specified.');
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false, false, 'g');
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = 'Guests can\'t use user lists. To access this feature, please create your own account.';
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('user-list-'.$user_id, 3, 3);

    // get the target's ID
    $target_id = (int) name_to_id($pdo, $target_name);

    // don't let user ignore themselves
    if ($mode === 'add' && $list === 'ignored' && $user_id === $target_id) {
        throw new Exception('You can\'t ignore yourself, silly!');
    }

    // create function
    $func_pre = trim($list, 's'); // trim trailing "s" off "friends"
    $func_suf = $mode === 'add' ? 'insert' : 'delete';
    $func = $func_pre . '_' . $func_suf;

    // perform the modification
    $func($pdo, $user_id, $target_id);

    // craft return message
    $msg = htmlspecialchars($target_name, ENT_QUOTES);
    switch ($list) {
        case 'friends':
            $l = [0 => ['added to', '!'], 1 => ['removed from', '.']];
            $l2u = $l[(int) !($mode === 'add')];
            $msg .= " has been $l2u[0] your friends list$l2u[1]";
            break;
        case 'ignored':
            $l = [0 => ['', 'won\'t', ' any', 'or', 'from them'], 1 => ['un-', 'will now', '', 'and', 'they send you']];
            $l2u = $l[(int) !($mode === 'add')];
            $msg .= " has been {$l2u[0]}ignored. You $l2u[1] receive$l2u[2] chat $l2u[3] private messages $l2u[4].";
            break;
        default:
            break; // should never happen
    }

    // tell it to the world
    $ret->success = true;
    $ret->message = $msg;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
