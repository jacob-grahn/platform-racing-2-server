<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;

$ip = get_ip();
$testing = false;

try {
    $pdo = pdo_connect();
    if (token_login($pdo) !== 3483035 || strpos($ip, $BLS_IP_PREFIX) === false) {
        http_response_code(404);
    } elseif ($testing !== true) {
        echo 'Error: Testing mode is disabled.';
    } else {
        echo 'not testing anything rn';
    }
    die();
} catch (Exception $e) {
}
