<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/Encryptor.php';
require_once __DIR__ . '/../fns/email_fns.php';
require_once __DIR__ . '/../queries/users/user_select.php';
require_once __DIR__ . '/../queries/users/user_update_email.php';
require_once __DIR__ . '/../queries/changing_emails/changing_email_insert.php';
require_once __DIR__ . '/../queries/changing_emails/changing_email_select.php';
require_once __DIR__ . '/../queries/changing_emails/changing_email_complete.php';


$encrypted_data = $_POST['data'];

$ip = get_ip();

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only change your email from an approved site such as pr2hub.com.");
    }

    // rate limiting
    rate_limit('change-email-attempt-'.$ip, 5, 1);

    //--- sanity check
    if (!isset($encrypted_data)) {
        throw new Exception('No data was recieved.');
    }

    //--- decrypt
    $encryptor = new Encryptor();
    $encryptor->set_key($CHANGE_EMAIL_KEY);
    $str_data = $encryptor->decrypt($encrypted_data, $CHANGE_EMAIL_IV);
    $data = json_decode($str_data);
    $new_email = $data->email;
    $pass = $data->pass;

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('change-email-attempt-'.$user_id, 5, 1);

    // get user info
    $user = user_select($pdo, $user_id);
    $user_name = $user->name;
    $old_email = $user->email;

    // check password
    pass_login($pdo, $user_name, $pass);

    // sanity check: check for guest
    if ($user->power < 1) {
        throw new Exception("Guests don't even really have accounts...");
    }

    // sanity check: check for invalid email
    if (!valid_email($new_email)) {
        throw new Exception("Invalid email address.");
    }

    // rate limiting
    rate_limit('change-email-'.$user_id, 86400, 2, 'Your email can be changed a maximum of two times per day.');
    rate_limit('change-email-'.$ip, 86400, 2, 'Your email can be changed a maximum of two times per day.');

    // set their email if they don't already have one
    if (is_empty($old_email)) {
        $time = time();
        $code = "set-email-$time";

        // log and do it
        changing_email_insert($pdo, $user_id, $old_email, $new_email, $code, $ip);
        $change = changing_email_select($pdo, $code);
        changing_email_complete($pdo, $change->change_id, $ip);
        user_update_email($pdo, $user_id, $old_email, $new_email);

        // tell the user and end the script
        $ret = new stdClass();
        $ret->message = 'Your email was changed successfully!';
        echo json_encode($ret);
        die();
    }

    // initiate an email change confirmation (generate code) if they do already have an email address
    $code = random_str(24);
    changing_email_insert($pdo, $user_id, $old_email, $new_email, $code, $ip);

    // send a confirmation email
    $from = 'Fred the Giant Cactus <contact@jiggmin.com>';
    $to = $old_email;
    $subject = 'PR2 Email Change Confirmation';
    $body = "Howdy {htmlspecialchars($user_name)},\n\nWe received a request to change the email on your account from {htmlspecialchars($old_email)} to {htmlspecialchars($new_email)}. If you requested this change, please click the link below to change the email address on your Platform Racing 2 account.\n\nhttp://pr2hub.com/account_confirm_email_change.php?code=$code\n\nIf you didn't request this change, you may need to change your password.\n\nAll the best,\nFred";
    send_email($from, $to, $subject, $body);

    // tell it to the world
    $ret = new stdClass();
    $ret->message = 'Almost done! We just sent a confirmation email to your old email address. Until you confirm the change, your old email address will still be active.';
    echo json_encode($ret);
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
    echo json_encode($ret);
}
