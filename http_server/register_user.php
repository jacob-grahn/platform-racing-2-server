<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/to_hash.php';
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/messages.php';

$name = trim(default_post('name'));
$password = default_post('password');
$time = time();
$ip = get_ip();

// sanitize email
$problematic_chars = array('&', '"', "'", "<", ">");
$email = str_replace($problematic_chars, '', $_POST['email']);

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    require_trusted_ref('create new accounts');

    // rate limiting (check if the IP address is spamming)
    $rl_msg = 'Please wait at least 10 seconds before trying to create another account.';
    rate_limit('register-account-attempt-'.$ip, 10, 2, $rl_msg);

    // error check
    if (is_empty($name) || !is_string($password) || is_empty($password)) {
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
    if (!is_empty($email) && !valid_email($email)) {
        $email = htmlspecialchars($email, ENT_QUOTES);
        throw new Exception("'$email' is not a valid email address.");
    }
    if (is_obscene($name)) {
        throw new Exception('Keep your username clean, pretty please!');
    }
    $test_name = preg_replace("/[^a-zA-Z0-9-.:;=?~! ]/", "", $name);
    if ($test_name != $name) {
        throw new Exception('Your name is invalid. You may only use alphanumeric characters, spaces and !-.:;=?~.');
    }

    // connect
    $pdo = pdo_connect();

    // check if banned
    check_if_banned($pdo, -1, $ip);

    // check if this name has been taken already
    if (user_select_by_name($pdo, $name, true) !== false) {
        throw new Exception('Sorry, that name has already been registered.');
    }

    // more rate limiting (check if too many accounts have been made from this ip today)
    $rl_msg = 'You may create a maximum of five accounts from the same IP address per day.';
    rate_limit('register-account-'.$ip, 86400, 5, $rl_msg);

    // register user
    do_register_user($pdo, $name, $password, $ip, $time, $email);

    $ret = new stdClass();
    $ret->success = true;
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
