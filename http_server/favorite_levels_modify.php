<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/favorite_levels.php';

$level_id = (int) default_post('level_id', 0);
$mode = default_post('mode', '');

$allowed_modes = ['add', 'remove'];

$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('favorite-levels-modify-'.$ip, 3, 3);

    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // sanity check: was a level ID sent?
    if ($level_id <= 0) {
        throw new Exception('A level ID wasn\'t received.');
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
        $e = 'Guests can\'t perform this action. To access this feature, please create your own account.';
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('favorite-levels-modify-'.$user_id, 3, 3);

    // get level title + sanity check: does this level exist?
    $level_title = level_select_title($pdo, $level_id);

    // perform the modification
    $func = 'favorite_level_' . ($mode === 'add' ? 'insert' : 'delete');
    $func($pdo, $user_id, $level_id);

    // craft return message
    $level_title = htmlspecialchars($level_title, ENT_QUOTES);
    $moded = $mode === 'add' ? 'added to' : 'removed from';
    $punc = $mode === 'add' ? '!' : '.';
    $msg = "$level_title has been $moded your favorites$punc It may take up to 30 seconds for your list to update.";

    // tell it to the world
    $ret->success = true;
    $ret->message = $msg;
    $ret->mode = $mode;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
