<?php

function users_reset_status($pdo)
{
    $result = $pdo->exec('
        UPDATE users
           SET status = "offline"
         WHERE time < UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)
    ');

    if ($result === false) {
        throw new Exception('Could not reset user statuses.');
    }

    return $result;
}
