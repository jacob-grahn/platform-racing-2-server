<?php

header("Content-type: text/plain");
require_once '../fns/all_fns.php';

$ip = get_ip();

try {

    // rate limiting
    rate_limit('random-level-'.$ip, 10, 1, "Please wait at least 10 seconds before generating another random level.");
    
    // connect
    $db = new DB();

    // get a random level
    $results = $db->call('levels_select_by_rand');
    $rows = $db->to_array($results);
    echo json_encode($rows);
    
    // end it, yo
    die();
    
}

catch(Exception $e){
    $message = $e->getMessage();
    echo "Error: $message";
    die();
}
