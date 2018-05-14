<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pr2/register_user_fns.php';

$name = $_POST['name'];
$password = $_POST['password'];
$time = time();
$ip = get_ip();

// sanitize email
$problematic_chars = array('&', '"', "'", "<", ">");
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$email = str_replace($problematic_chars, '', $email);

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    require_trusted_ref('create new accounts');

    // rate limiting (check if the IP address is spamming)
    rate_limit(
        'register-account-attempt-'.$ip,
        10,
        2,
        'Please wait at least 10 seconds before trying to create another account.'
    );

    // error check
    if (empty($name) || !is_string($password) || $password == '') {
        throw new Exception('You must enter a name and a password to register an account.');
    }
    if (trim(strlen($name)) < 2) {
        throw new Exception('Your name must be at least 2 characters long.');
    }
    if (trim(strlen($name)) > 20) {
        throw new Exception('Your name can not be more than 20 characters long.');
    }
    if (strpos($name, '`') !== false) {
        throw new Exception('The ` character is not allowed.');
    }
    if ($email != '' && !valid_email($email)) {
        throw new Exception("'$email' is not a valid email address.");
    }
    if (is_obscene($name)) {
        throw new Exception('Keep your username clean, pretty please!');
    }
    $test_name = preg_replace("/[^a-zA-Z0-9-.:;=?~! ]/", "", $name);
    if ($test_name != $name) {
        throw new Exception('Your name is invalid. You may only use alphanumeric characters, spaces and !-.:;=?~.');
    }

    // connect to the db
    $pdo = pdo_connect();

    // check if banned
    check_if_banned($pdo, -1, $ip);

    // check if this name has been taken already
    $existing_user = user_select_by_name($pdo, $name, true);
    if ($existing_user) {
        throw new Exception('Sorry, that name has already been registered.');
    }

    // more rate limiting (check if too many accounts have been made from this ip today)
    rate_limit(
        'register-account-'.$ip,
        86400,
        5,
        'You may create a maximum of five accounts from the same IP address per day.'
    );

    // register user
    do_register_user($pdo, $name, $password, $ip, $time, $email);

    $ret = new stdClass();
    $ret->result = 'success';
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
} finally {
    echo json_encode($ret);
    die();
}
