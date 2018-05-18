<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/friends/friend_delete.php';

$friend_name = $_POST['target_name'];
$safe_friend_name = htmlspecialchars($friend_name);
$ip = get_ip();

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('friends-list-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't add/remove users to/from their friends/ignored lists. ".
            "To access this feature, please create your own account."
        );
    }

    // more rate limiting
    rate_limit('friends-list-'.$user_id, 3, 2);

    // get the id of the player they're removing as a friend
    $friend_id = name_to_id($pdo, $friend_name);

    // delete the friendship :(
    friend_delete($pdo, $user_id, $friend_id);

    // tell the world
    echo "message=$safe_friend_name has been removed from your friends list.";
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
