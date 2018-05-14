<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/messages/message_delete.php';

$message_id = $_POST['message_id'];
$ip = get_ip();

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    
    // referrer check
    require_trusted_ref('delete PMs');

    // rate limiting
    rate_limit('message-delete'.$ip, 5, 2, "Please wait at least 5 seconds before trying to delete another PM.");

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo);
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't use the private messaging system. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit('message-delete'.$user_id, 5, 2, "Please wait at least 5 seconds before trying to delete another PM.");

    // delete the message from the database
    message_delete($pdo, $user_id, $message_id);

    // tell the world
    echo 'success=true';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
    exit;
}
