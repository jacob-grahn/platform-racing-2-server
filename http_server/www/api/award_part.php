<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../../fns/all_fns.php';

$user_name = find('user_name');
$safe_name = htmlspecialchars($user_name);
$pass = find('pass');
$type = find('type');
$part_id = find('part_id');

try {
    if ($pass != $PR2_HUB_API_PASS) {
        throw new Exception('Incorrect pass.');
    }

    //connecto!!!
    $db = new DB();
    $pdo = pdo_connect();

    //get the to id
    $target_id = name_to_id($pdo, $user_name);

    //give the player the part
    $result = award_part($pdo, $target_id, $type, $part_id);
    if (!$result) {
        throw new Exception('They already have the part.');
    }

    //tell the world
    $ret = new stdClass();
    $ret->success = true;
    $ret->message = "The part was given to $safe_name.";
    echo json_encode($ret);
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
    echo json_encode($ret);
}
