<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';

$level_id = (int) default_post('course_id', 0);
$hash = default_post('hash', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('level-pass-'.$ip, 3, 2);

    // sanity
    if (is_empty($level_id, false) || is_empty($hash)) {
        throw new Exception('Some data was missing.');
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo, false);

    // more rate limiting
    rate_limit('level-pass-'.$user_id, 3, 2);

    // check the pass
    $hash2 = sha1($hash . $LEVEL_PASS_SALT);
    $level = level_select($pdo, $level_id);
    $match = $level->pass === $hash2;
    if (!$match) {
        sleep(1);
    }

    // return info
    $res = new stdClass();
    $res->access = $match;
    $res->level_id = (int) $level_id;
    $res->user_id = (int) $user_id;
    $str_result = json_encode($res);

    // set up encryptor
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($LEVEL_PASS_KEY);
    $enc_result = $encryptor->encrypt($str_result, $LEVEL_PASS_IV);

    $ret->success = true;
    $ret->result = $enc_result;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
