<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';

$data = default_post('i', '');
$token = default_post('token', '');
$from_lobby = default_post('from_lobby', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('logout-'.$ip, 5, 2, 'Please wait at least 5 seconds before attempting to log out again.');
    rate_limit('logout-'.$ip, 60, 10, 'Only 10 logout requests per minute per IP are accepted.');

    // connect to the db
    $pdo = pdo_connect();

    // client has token, token sent via post
    if (!is_empty($_POST['token']) && is_empty($data)) {
        $ret->errorType = 'token';
        if (token_delete($pdo, $token, true) === false) {
            throw new Exception('Could not find this token in the database.');
        }
        setcookie("token", "", time() - 3600);
    } // client doesn't have token, user pass is used to verify an token logout from cookie instead
    elseif (isset($data)) {
        $encryptor = new \pr2\http\Encryptor();
        $encryptor->setKey($LOGIN_KEY);
        $str_logout = $encryptor->decrypt($data, $LOGIN_IV);
        $logout = json_decode($str_logout);
        $user_name = $logout->user_name;
        $user_pass = $logout->user_pass;

        // check login and delete from cookie data if details are correct
        $ret->errorType = 'pass';
        pass_login($pdo, $user_name, $user_pass);
        if (isset($_COOKIE['token'])) {
            $ret->errorType = 'db';
            token_delete($pdo, $_COOKIE['token']);
        } else {
            $ret->errorType = 'no_token';
            throw new Exception('No token to delete. You can log in as normal!');
        }
    }

    // if no errors were triggered, clear the errorType
    $ret->success = true;
    unset($ret->errorType);
} catch (Exception $e) {
    if (!is_empty($from_lobby)) {
        $ret->error = $e->getMessage();
    }
} finally {
    die(json_encode($ret));
}
