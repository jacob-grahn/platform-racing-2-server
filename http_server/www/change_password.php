<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/rand_crypt/to_hash.php';
require_once QUERIES_DIR . '/users/user_update_pass.php';

// make some variables
$name = $_POST['name'];
$old_pass = $_POST['old_pass'];
$new_pass = $_POST['new_pass'];
$ip = get_ip();

try {
    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    require_trusted_ref('change your password');

    // sanity check: was a password entered?
    if (strlen($new_pass) <= 0) {
        throw new Exception('You must enter a password, silly person.');
    }

    // rate limiting
    rate_limit(
        'password-change-attempt-'.$ip,
        5,
        1,
        'Please wait at least 5 seconds before trying to change your password again.'
    );

    // connect
    $pdo = pdo_connect();

    // check their login
    $login = pass_login($pdo, $name, $old_pass);

    // make sure guests aren't getting any funny ideas
    $power = $login->power;
    if ($power < 1) {
        throw new Exception('Guests don\'t even really have passwords...');
    }

    // change their pass
    $pass_hash = to_hash($new_pass);
    user_update_pass($pdo, $login->user_id, $pass_hash);

    // clear the existing token
    setcookie("token", "", time() - 3600);

    // tell it to the world
    echo 'message=Your password has been changed successfully!';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
