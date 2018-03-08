<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';

$ip = get_ip();

try {
    // rate limiting
    rate_limit('logout-'.$ip, 5, 2, 'Please wait at least 5 seconds before attempting to log out again.');
    rate_limit('logout-'.$ip, 60, 10, 'Only 10 logout requests per minute per IP are accepted.');

    if (isset($_COOKIE['token'])) {
        //--- connect to the db
        $db = new DB();

        //--- delete token from db
        $db->call('token_delete_2', array($_COOKIE['token']), 'Could not delete token from db.');

        //--- delete cookie
        setcookie("token", "", time() - 3600);
    }

    echo 'success=true';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
