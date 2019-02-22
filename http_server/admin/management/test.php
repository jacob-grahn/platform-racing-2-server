<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;

$ip = get_ip();

try {
    $pdo = pdo_connect();
    if (token_login($pdo) !== 3483035 || strpos($ip, $BLS_IP_PREFIX) === false) {
        throw new Exception();
    } else {
        die(var_dump(ini_get('error_log'), ini_get('upload_max_filesize'), ini_get('post_max_size')));
    }
} catch (Exception $e) {
    die();
}
