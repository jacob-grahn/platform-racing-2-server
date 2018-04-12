<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/tokens/token_delete.php';

header("Content-type: text/plain");

$ip = get_ip();

try {
    // rate limiting
    rate_limit('logout-'.$ip, 5, 2, 'Please wait at least 5 seconds before attempting to log out again.');
    rate_limit('logout-'.$ip, 60, 10, 'Only 10 logout requests per minute per IP are accepted.');

    if (is_from_game() !== true){
     throw new Exception("It looks like you're not using PR2 to log out. For security reasons, you may only log out from a PR2 client.");   
    }
    
    if (isset($_COOKIE['token'])) {
        // connect to the db
        $pdo = pdo_connect();

        // delete token from db
        token_delete($pdo, $_COOKIE['token']);

        // delete cookie
        setcookie("token", "", time() - 3600);
    }

    echo 'success=true';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}

function is_from_game() 
{
    $is_from_game = false;
    
    if (
           (
           !is_empty($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
           strpos($_SERVER["HTTP_X_REQUESTED_WITH"], "ShockwaveFlash/") !== 0
           ) ||
       is_empty($_SERVER["HTTP_REFERER"])
    ) {
         $is_from_game = true;
    }
    
    return $is_from_game;
}
