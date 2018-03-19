<?php

function login_attempts_truncate($pdo)
{
    $result = $pdo->exec('TRUNCATE TABLE login_attempts');

    if ($result === false) {
        throw new Exception('could not truncate login_attempts');
    }

    return $result;
}
