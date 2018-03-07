<?php

header("Content-type: text/plain");

require_once '../fns/all_fns.php';
require_once '../fns/pr2_fns.php';

$message = default_val($_POST['message']);
$ip = get_ip();

try {
    
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    
    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only message your guild from an approved site such as pr2hub.com.");
    }
    
    // rate limit
    rate_limit('guildMessage-attempt-'.$ip, 15, 1, "Please wait at least 15 seconds before trying to message your guild again.");
    
    // connect
    $db = new DB();
    
    // confirm login
    $user_id = token_login($db, false);
    
    // confirm that they are in a guild
    $guild_id = $db->grab('guild', 'user_select', array($user_id));
    if($guild_id <= 0) {
        throw new Exception('You are not in a guild.');
    }
    
    // confirm that there's a message
    if(is_empty($message)) {
        throw new Exception('You must enter a valid message.');
    }
    
    // rate limit
    rate_limit('guildMessage-'.$ip, 300, 1, 'Only one guild message can be sent every five minutes.');
    rate_limit('guildMessage-'.$user_id, 300, 1, 'Only one guild message can be sent every five minutes.');
    
    // send message to each member
    $members = $db->to_array($db->call('guild_select_members', array($guild_id)));
    foreach( $members as $member ) {
        $db->call('message_insert', array($member->user_id, $user_id, $message, $ip));
    }
    
    echo 'message=Your message was sent successfully!';        
}

catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}

?>
