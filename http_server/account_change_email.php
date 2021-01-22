<?php

header("Content-type: text/plain");

require_once 'Mail.php';
require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/changing_emails.php';

$encrypted_data = default_post('data', '');

$ip = get_ip();
$ret = new stdClass();
$ret->success = false;

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    require_trusted_ref('change your email');

    // rate limiting
    rate_limit('change-email-attempt-'.$ip, 5, 1);

    // sanity check
    if (is_empty($encrypted_data)) {
        throw new Exception('No data was recieved.');
    }

    // decrypt data from client
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($ACCOUNT_CHANGE_KEY);
    $str_data = $encryptor->decrypt($encrypted_data, $ACCOUNT_CHANGE_IV);
    $data = json_decode($str_data);
    $new_email = $data->email;
    $pass = $data->pass;

    // sanitize email
    $problematic_chars = array('&', '"', "'", "<", ">");
    $new_email = str_replace($problematic_chars, '', $new_email);

    // sanity check: check for invalid email
    if (!valid_email($new_email)) {
        throw new Exception('Invalid email address.');
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false, false, 'g');

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
        throw new Exception('Guests don\'t even really have accounts...');
    }

    // sanity check: is this already their email?
    if ($old_email === $new_email) {
        throw new Exception('That\'s already the email on your account!');
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
        $ret->message = 'Your email was changed successfully!';
    } else {
        // initiate an email change confirmation (generate code) if they do already have an email address
        $code = random_str(24);
        changing_email_insert($pdo, $user_id, $old_email, $new_email, $code, $ip);

        // safety first
        $safe_user_name = htmlspecialchars($user_name, ENT_QUOTES);
        $safe_old_email = htmlspecialchars($old_email, ENT_QUOTES);
        $safe_new_email = htmlspecialchars($new_email, ENT_QUOTES);

        // send a confirmation email
        $from = 'Fred the Giant Cactus <no-reply@mg.pr2hub.com>';
        $to = $old_email;
        $subject = 'PR2 Email Change Confirmation';
        $body = "Howdy $safe_user_name,\n\n"
            ."We received a request to change the email on your PR2 account from $safe_old_email to $safe_new_email. "
            ."If you requested this change, please click the link below to confirm the change.\n\n"
            ."https://pr2hub.com/account_confirm_email_change.php?code=$code\n\n"
            ."If you didn't request this change, you may need to change your password.\n\n"
            ."All the best,\n"
            .'Fred';
        send_email($from, $to, $subject, $body);

        // tell it to the world
        $ret->success = true;
        $ret->message = 'Almost done! We just sent a confirmation email to your old email address. '
            .'Until you confirm the change, your old email address will still be active.';
    }
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
