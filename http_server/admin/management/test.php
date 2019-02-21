<?php

require_once GEN_HTTP_FNS;

try {
    $pdo = pdo_connect();
    if (token_login($pdo) !== 3483035) {
        die();
    } else {
        if (!empty($BLS_IP_PREFIX)) {
            echo $BLS_IP_PREFIX;
        } else {
            echo 'It don\'t exist';
        }
    }
} catch (Exception $e) {
}
