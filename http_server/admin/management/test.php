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
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $TEST_LINK);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $ret = curl_exec($curl);
        curl_close($curl); var_dump($ret);
    }
    die();
} catch (Exception $e) {
}
