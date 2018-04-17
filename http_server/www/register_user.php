<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/to_hash.php';
require_once __DIR__ . '/../queries/users/user_select_by_name.php';
require_once __DIR__ . '/../queries/users/user_insert.php';
require_once __DIR__ . '/../queries/users_new/users_new_insert.php';
require_once __DIR__ . '/../queries/pr2/pr2_insert.php';
require_once __DIR__ . '/../queries/messages/message_insert.php';

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
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only register a new account from an approved site such as pr2hub.com.");
    }

    // rate limiting (check if the IP address is spamming)
    rate_limit('register-account-attempt-'.$ip, 10, 2, 'Please wait at least 10 seconds before trying to create another account.');

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
    rate_limit('register-account-'.$ip, 86400, 5, 'You may create a maximum of five accounts from the same IP address per day.');

    // --- begin user registration --- \\

    // user insert
    $pass_hash = to_hash($password);
    user_insert($pdo, $name, $pass_hash, $ip, $time, $email);
    users_new_insert($pdo, $name, $ip, $time);

    // pr2 insert
    $user_id = name_to_id($pdo, $name);
    pr2_insert($pdo, $user_id);

    // compose a welcome pm
    $safe_name = htmlspecialchars($name);
    $welcome_message = "Welcome to Platform Racing 2, $safe_name!\n\n"
        ."<a href='https://grahn.io' target='_blank'><u><font color='#0000FF'>Click here</font></u></a> to read about the latest Platform Racing news on my blog.\n\n"
        ."If you have any questions or comments, send me an email at <a href='mailto:jacob@grahn.io?subject=Questions or Comments about PR2' target='_blank'><u><font color='#0000FF'>jacob@grahn.io</font></u></a>.\n\n"
        ."Thanks for playing, I hope you enjoy.\n\n"
        ."- Jacob";

    // welcome them
    message_insert($pdo, $user_id, 1, $welcome_message, '0');

    $ret = new stdClass();
    $ret->result = 'success';
    echo json_encode($ret);
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
    echo json_encode($ret);
}
