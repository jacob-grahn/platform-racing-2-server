<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/ignored.php';

$ignored_name = default_post('target_name', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // rate limiting
    rate_limit('ignored-list-'.$ip, 3, 2);

    // sanity check: was a name sent?
    if (is_empty($ignored_name)) {
        throw new Exception('Who are you trying to ignore?');
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false);
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = 'Guests can\'t use user lists. To access this feature, please create your own account.';
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('ignored-list-'.$user_id, 3, 2);

    // get the ignored user's id
    $ignored_id = (int) name_to_id($pdo, $ignored_name);
    if ($ignored_id === $user_id) {
        throw new Exception("You can't ignore yourself, silly!");
    }

    // create the restraining order
    ignored_insert($pdo, $user_id, $ignored_id);

    // tell it to the world
    $safe_ignored_name = htmlspecialchars($ignored_name);
    $ret->success = true;
    $ret->message = "$safe_ignored_name has been ignored. You won't recieve any chat or private messages from them.";
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
