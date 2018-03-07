<?php

header("Content-type: text/plain");

require_once '../fns/all_fns.php';
require_once '../fns/to_hash.php';

// make some variables
$name = $_POST['name'];
$old_pass = $_POST['old_pass'];
$new_pass = $_POST['new_pass'];
$ip = get_ip();

// safety first
$safe_name = addslashes($name);

try {
    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    
    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only change your password from an approved site such as pr2hub.com.");
    }
    
    // sanity check: was a password entered?
    if (strlen($new_pass) <= 0) {
        throw new Exception('You must enter a password, silly person.');
    }
    
    // rate limiting
    rate_limit('password-change-attempt-'.$ip, 5, 1, 'Please wait at least 5 seconds before trying to change your password again.');

    // connect
    $db = new DB();

    // check their login
    $login = pass_login($db, $name, $old_pass);

    // make sure guests aren't getting any funny ideas
    $power = $login->power;
    if ($power < 1) {
        throw new Exception('Guests don\'t even really have passwords...');
    }
    
    // change their pass
    $pass_hash = to_hash($new_pass);
    $safe_pass_hash = addslashes($pass_hash);
    $result = $db->query(
        "UPDATE users
						SET pass_hash = '$safe_pass_hash'
						WHERE name = '$safe_name'"
    );

    if (!$result) {
        throw new Exception('Could not update your password. Sorries.');
    }

    // clear the existing token
    setcookie("token", "", time() - 3600);

    // tell it to the world
    echo 'message=Your password has been changed successfully!';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
