<?php

header("Content-type: text/plain");
require_once '../fns/all_fns.php';

$message_id = $_POST['message_id'];
$ip = get_ip();

try {
    
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    
    // rate limiting
    rate_limit('message-delete'.$ip, 5, 2, "Please wait at least 5 seconds before trying to delete another PM.");
    
    // connect
    $db = new DB();
    
    // check their login
    $user_id = token_login($db);
    
    // more rate limiting
    rate_limit('message-delete'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to delete another PM.");
    
    // delete the message from the database
    $db->call('message_delete', array($user_id, $message_id));
    
    // tell the world
    echo 'success=true';
    
}
catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
    exit;
}
?>
