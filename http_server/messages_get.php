<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/messages.php';

$start = (int) find_no_cookie('start', 0);
$count = (int) find_no_cookie('count', 10);
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('get-messages-'.$ip, 3, 2);
    rate_limit('get-messages-'.$ip, 60, 10);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo);
    $power = (int) user_select_power($pdo, $user_id);

    // more rate limiting
    rate_limit('get-messages-'.$user_id, 3, 2);
    rate_limit('get-messages-'.$user_id, 60, 10);

    $largest_id = 0;
    $messages = messages_select($pdo, $user_id, $start, $count);
    $messages_array = array();
    foreach ($messages as $row) {
        if ($power <= 0 && $largest_id <= 0) {
            $message = new stdClass();
            $message->message_id = 0;
            $message->message = "Hi there! It looks like you're a guest. "
                ."You won't be able to send or receive private messages.\n\n"
                ."To use the private messaging system, log out and create your own account.\n\n"
                ."Thanks for playing!\n- Jiggmin";
            $message->time = time();
            $message->user_id = 1;
            $message->name = 'Jiggmin';
            $message->group = 3;
            $messages_array[] = $message;
            break;
        }

        $largest_id = $row->message_id > $largest_id ? (int) $row->message_id : $largest_id;
        $from_user = user_select_name_and_power($pdo, $row->from_user_id);

        // make message and add to array
        $message = new stdClass();
        $message->message_id = (int) $row->message_id;
        $message->message = $row->message;
        $message->time = (int) $row->time;
        $message->user_id = (int) $row->from_user_id;
        $message->name = $from_user->name;
        $message->group = (int) $from_user->power;
        $messages_array[] = $message;
    }

    if ($start === 0) {
        user_update_read($pdo, $user_id, $largest_id);
    }

    $ret->success = true;
    $ret->messages = $messages_array;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
