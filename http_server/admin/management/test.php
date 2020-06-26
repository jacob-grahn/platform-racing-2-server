<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;

$ip = get_ip();
$testing = true;

try {
    $pdo = pdo_connect();
    if (/*token_login($pdo) !== 3483035 || */strpos($ip, $BLS_IP_PREFIX) === false) {
        http_response_code(404);
    } elseif ($testing !== true) {
        echo 'Error: Testing mode is disabled.';
    } else {
        var_dump(file_get_contents($TEST_LINK));
    }
    die();
} catch (Exception $e) {
}
