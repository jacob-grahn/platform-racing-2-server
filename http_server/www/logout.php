<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/tokens/token_delete.php';

header("Content-type: text/plain");

function is_from_game(){
 if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && !empty($_SERVER["HTTP_X_REQUESTED_WITH"])){
  if (strpos($_SERVER["HTTP_X_REQUESTED_WITH"], "ShockwaveFlash/") !== 0){
   return true;
  }
     else
  {
      return false;   
  }
 }
 else
 {
    return false;
 }
}

$ip = get_ip();

try {
    // rate limiting
    rate_limit('logout-'.$ip, 5, 2, 'Please wait at least 5 seconds before attempting to log out again.');
    rate_limit('logout-'.$ip, 60, 10, 'Only 10 logout requests per minute per IP are accepted.');

    if (is_from_game() !== true){
     throw new Exception("For security reasons logout can only be performed from game");   
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
