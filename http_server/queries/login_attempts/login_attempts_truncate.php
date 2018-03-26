<?php

function login_attempts_truncate($pdo)
{
    $result = $pdo->exec('TRUNCATE TABLE login_attempts');

    if ($result === false) {
        throw new Exception('Could not truncate table login_attempts.');
    }

    return $result;
}
