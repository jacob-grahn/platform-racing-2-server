<?php

header("Content-type: text/plain");
require_once '../fns/all_fns.php';

$x = (int) find('x', 0);
$y = (int) find('y', 0);
$level_id = (int) find('levelId', 0);
$ip = get_ip();

try {
    
    // sanity check: is data missing?
    if (is_empty($x, false) || is_empty($y, false) || is_empty($level_id, false)) {
        throw new Exception("Some data is missing.");
    }
    
    // check referrer
    $ref = check_ref();
    if ($ref !== true && $ref != '') {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only set the artifact from an approved site such as pr2hub.com.");
    }
    
    // rate limiting
    rate_limit('place-artifact-attempt-'.$ip, 30, 1, "Please wait at least 30 seconds before trying to set a new artifact location again.");
    
    // connect
    $db = new DB();
    
    // check their login
    $user_id = token_login($db);
    
    // more rate limiting
    if ($user_id != 1) {
        rate_limit('place-artifact-'.$ip, 3600, 10, "The artifact can only be placed a maximum of 10 times per hour. Try again later.");
        rate_limit('place-artifact-'.$user_id, 3600, 10, "The artifact can only be placed a maximum of 10 times per hour. Try again later.");
    }
    
    // sanity check: are they Fred?
    if($user_id != 1 && $user_id != 4291976 ) {
        throw new Exception('You are not Fred.');
    }
    
    // update the artifact location in the database
    $db->call('artifact_location_update', array( $level_id, $x, $y ));
    
    // tell the world
    echo "message=Great success! The artifact location will be updated at the top of the next minute.";
    
}

catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}

?>
