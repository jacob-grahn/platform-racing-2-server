<?php

header("Content-type: text/plain");

require_once '../fns/all_fns.php';

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
    $db = new DB();
    
    // check their login
    $user_id = token_login($db);
    
    // more rate limiting
    rate_limit('get-messages-'.$user_id, 3, 2);
    rate_limit('get-messages-'.$user_id, 60, 10);
    
    $safe_user_id = $db->escape($user_id);
    $safe_start = $db->escape($start);
    $safe_count = $db->escape($count);
    $result = $db->query(
        "SELECT messages.message_id, messages.message, messages.time, messages.from_user_id
									FROM messages
									WHERE messages.to_user_id = '$safe_user_id'
									ORDER BY messages.time desc
									LIMIT $safe_start, $safe_count"
    );
    if (!$result) {
        throw new Exception('Could not retrieve messages.');
    }
    
    
    while ($row = $result->fetch_object()) {
        if ($row->message_id > $largest_id) {
            $largest_id = $row->message_id;
        }
        
        $from_user = $db->grab_row('user_select', array( $row->from_user_id ));
        
        $message = new stdClass();
        $message->message_id = $row->message_id;
        $message->message = $row->message;
        $message->time = $row->time;
        $message->user_id = $row->from_user_id;
        $message->name = $from_user->name;
        $message->group = $from_user->power;
        
        $messages[] = $message;
    }
    
    if ($start == 0) {
        $db->call('users_inc_read', array($user_id, $largest_id));
    }
    
    $r = new stdClass();
    $r->messages = $messages;
    $r->success = true;
    echo json_encode($r);
} catch (Exception $e) {
    $r = new stdClass();
    $r->error = $e->getMessage();
    echo json_encode($r);
}
