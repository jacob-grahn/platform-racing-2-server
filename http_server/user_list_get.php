<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/follows.php';
require_once QUERIES_DIR . '/friends.php';
require_once QUERIES_DIR . '/ignored.php';

$mode = default_get('mode', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit("user-list-$ip", 5, 2);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, true, false, 'g');
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $e = 'Guests can\'t make user lists. To access this feature, please create your own account.';
        throw new Exception($e);
    }

    // more rate limiting
    rate_limit('user-list-'.$user_id, 5, 2);

    switch ($mode) {
        case 'following':
            $users = following_select_list($pdo, $user_id);
            break;
        case 'friends':
            $users = friends_select($pdo, $user_id);
            break;
        case 'ignored':
            $users = ignored_select_list($pdo, $user_id);
            break;
        default:
            throw new Exception('Invalid list mode specified.');
    }

    // make individual list entries
    $num = 0;
    $data = array();
    foreach ($users as $row) {
        $user = new stdClass();
        $user->name = $row->name;
        $user->group = get_group_info($row)->str;
        $user->status = strpos($row->status, 'Playing on ') !== false ? substr($row->status, 11) : $row->status;
        $user->rank = $row->rank + (!is_empty($row->used_tokens, false) ? $row->used_tokens : 0);
        $user->hats = count(explode(',', $row->hat_array)) - 1;
        $data[] = $user;
    }

    $ret->success = true;
    $ret->users = $data;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
