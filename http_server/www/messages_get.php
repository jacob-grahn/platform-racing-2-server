<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/messages/messages_select.php';
require_once __DIR__ . '/../queries/users/user_select.php';
require_once __DIR__ . '/../queries/users/user_update_read.php';


$start = (int) find('start', 0);
$count = (int) find('count', 10);
$messages = array();
$largest_id = 0;
$ip = get_ip();

try {
    // rate limiting
    rate_limit('get-messages-'.$ip, 3, 2);
    rate_limit('get-messages-'.$ip, 60, 10);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo);

    // more rate limiting
    rate_limit('get-messages-'.$user_id, 3, 2);
    rate_limit('get-messages-'.$user_id, 60, 10);

    $messages = messages_select($pdo, $user_id, $start, $count);

    foreach ($messages as $row) {
        if ($row->message_id > $largest_id) {
            $largest_id = $row->message_id;
        }

        $from_user = user_select($pdo, $row->from_user_id);

        $message = new stdClass();
        $message->message_id = $row->message_id;
        $message->message = $row->message;
        $message->time = $row->time;
        $message->user_id = $row->from_user_id;
        $message->name = $from_user->name;
        $message->group = $from_user->power;

        $messages_array[] = $message;
    }

    if ($start == 0) {
        user_update_read($pdo, $user_id, $largest_id);
    }

    $r = new stdClass();
    $r->messages = $messages_array;
    $r->success = true;
    echo json_encode($r);
} catch (Exception $e) {
    $r = new stdClass();
    $r->error = $e->getMessage();
    echo json_encode($r);
}
