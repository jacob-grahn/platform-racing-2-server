<?php

header("Content-type: text/plain");

require_once '../fns/all_fns.php';
require_once '../fns/Encryptor.php';
require_once '../fns/email_fns.php';

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
    $db = new DB();

    // check their login
    $user_id = token_login($db, false);
    
    // more rate limiting
    rate_limit('change-email-attempt-'.$user_id, 5, 1);
    
    // get user info
    $user = $db->grab_row('user_select', array($user_id));
    $user_name = $user->name;
    $old_email = $user->email;
    
    // make things safe
    $safe_old_email = htmlspecialchars($old_email);
    $safe_new_email = htmlspecialchars($new_email);
    $safe_name = htmlspecialchars($user_name);

    // check password
    pass_login($db, $user_name, $pass);

    // sanity check: check for guest
    if ($user->power < 1) {
        throw new Exception("Guests don't even really have accounts...");
    }
    
    // sanity check: check for invalid email
    if (!valid_email($new_email)) {
        throw new Exception("\"$safe_new_email\" is not a valid email address.");
    }
    
    // rate limiting
    rate_limit('change-email-'.$user_id, 86400, 2, 'Your email can be changed a maximum of two times per day.');
    rate_limit('change-email-'.$ip, 86400, 2, 'Your email can be changed a maximum of two times per day.');

    // set their email if they don't already have one
    if (is_empty($old_email)) {
        $time = time();
        $code = "set-email-$time";
        
        // log and do it
        $db->call('changing_email_insert', array($user_id, $old_email, $new_email, $code, $ip));
        $change_id = $db->grab('change_id', 'changing_email_select', array($code));
        $db->call('changing_email_complete', array($change_id, $ip));
        $db->call('user_update_email', array($user_id, $old_email, $new_email));
        
        // tell the user and end the script
        $ret = new stdClass();
        $ret->message = 'Your email was changed successfully!';
        echo json_encode($ret);
        die();
    }

    // initiate an email change confirmation (generate code) if they do already have an email address
    $code = random_str(24);
    $db->call('changing_email_insert', array($user_id, $old_email, $new_email, $code, $ip));

    // send a confirmation email
    $from = 'Fred the Giant Cactus <contact@jiggmin.com>';
    $to = $old_email;
    $subject = 'PR2 Email Change Confirmation';
    $body = "Howdy $safe_name,\n\nWe received a request to change the email on your account from $safe_old_email to $safe_new_email. If you requested this change, please click the link below to change the email address on your Platform Racing 2 account.\n\nhttp://pr2hub.com/account_confirm_email_change.php?code=$code\n\nIf you didn't request this change, you may need to change your password.\n\nAll the best,\nFred";
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
