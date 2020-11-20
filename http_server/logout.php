<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';

$token = default_post('token', '');
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
    if (!is_empty($token)) {
        if (token_delete($pdo, $token, true) === false) {
            throw new Exception('Could not find this token in the database.');
        }
        setcookie("token", "", time() - 3600);
    }

    // if no errors were triggered, clear the errorType
    $ret->success = true;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
