<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/to_hash.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/users.php';

// make some variables
$data = default_post('i', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    require_trusted_ref('change your password');

    // sanity check: was any data sent?
    if (is_empty($data)) {
        throw new Exception('Well, that didn\'t work...');
    }

    // decrypt
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($LOGIN_KEY);
    $data = $encryptor->decrypt($data, $LOGIN_IV);
    $data_obj = json_decode($data);
    $name = $data_obj->name;
    $old_pass = $data_obj->old_pass;
    $new_pass = $data_obj->new_pass;

    // sanity check: was a password entered?
    if (strlen($old_pass) <= 0 || strlen($new_pass) <= 0) {
        throw new Exception('You must enter a password, silly person!');
    }

    // sanity check: are the old and new passwords different?
    if ($old_pass === $new_pass) {
        throw new Exception('Your current and new passwords match. Try picking a new password.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least 5 seconds before trying to change your password again.';
    rate_limit('password-change-attempt-'.$ip, 5, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // check their login
    $login = pass_login($pdo, $name, $old_pass, 'g');

    // make sure guests aren't getting any funny ideas
    $power = (int) $login->power;
    if ($power < 1) {
        throw new Exception('Guests don\'t even really have passwords...');
    }

    // change their pass
    user_update_pass($pdo, $login->user_id, to_hash($new_pass));

    // clear the existing token
    setcookie("token", "", time() - 3600);

    // tell the world
    $ret->success = true;
    $ret->message = 'Your password has been changed successfully!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
