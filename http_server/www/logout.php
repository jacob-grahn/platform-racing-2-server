<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/tokens/token_delete.php';

$data = $_POST['i'];
$token = $_POST['token'];
$from_lobby = $_POST['from_lobby'];
$ip = get_ip();

$ret = new stdClass();
$ret->success = true;

try {
    // rate limiting
    rate_limit('logout-'.$ip, 5, 2, 'Please wait at least 5 seconds before attempting to log out again.');
    rate_limit('logout-'.$ip, 60, 10, 'Only 10 logout requests per minute per IP are accepted.');

    // connect to the db
    $pdo = pdo_connect();

    // client has token, token sent via post
    if (isset($_POST['token']) && !isset($data)) {
        $ret->errorType = 'token';
        token_delete($pdo, $token);
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
    unset($ret->errorType);
} catch (Exception $e) {
    if (!isset($from_lobby)) {
        $ret->success = false;
        $ret->error = $e->getMessage();
    }
} finally {
    die(json_encode($ret));
}