<?php

header("Content-type: text/plain");

require_once 'Mail.php';
require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once HTTP_FNS . '/send_email.php';
require_once QUERIES_DIR . '/users/user_select.php';
require_once QUERIES_DIR . '/users/user_update_email.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_insert.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_select.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_complete.php';

$encrypted_data = $_POST['data'];

$ip = get_ip();

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
    if (!isset($encrypted_data)) {
        throw new Exception('No data was recieved.');
    }

    // decrypt data from client
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($CHANGE_EMAIL_KEY);
    $str_data = $encryptor->decrypt($encrypted_data, $CHANGE_EMAIL_IV);
    $data = json_decode($str_data);
    $new_email = $data->email;
    $pass = $data->pass;

    // sanitize email
    $problematic_chars = array('&', '"', "'", "<", ">");
    $new_email = str_replace($problematic_chars, '', $new_email);

    // sanity check: check for invalid email
    if (!valid_email($new_email)) {
        throw new Exception("Invalid email address.");
    }

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

    // safety first
    $safe_user_name = htmlspecialchars($user_name);
    $safe_old_email = htmlspecialchars($old_email);
    $safe_new_email = htmlspecialchars($new_email);

    // send a confirmation email
    $from = 'Fred the Giant Cactus <contact@jiggmin.com>';
    $to = $old_email;
    $subject = 'PR2 Email Change Confirmation';
    $body = "Howdy $safe_user_name,\n\nWe received a request to change the "
        ."email on your account from $safe_old_email to $safe_new_email. "
        ."If you requested this change, please click the link below to change "
        ."the email address on your Platform Racing 2 account.\n\n"
        ."http://pr2hub.com/account_confirm_email_change.php?code=$code\n\n"
        ."If you didn't request this change, you may need to change your "
        ."password.\n\nAll the best,\nFred";
    send_email($from, $to, $subject, $body);

    // tell it to the world
    $ret = new stdClass();
    $ret->message = 'Almost done! We just sent a confirmation email to your '
        .'old email address. Until you confirm the change, your old email '
        .'address will still be active.';
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
} finally {
    echo json_encode($ret);
    die();
}
