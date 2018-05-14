<?php

function bans_delete_old($pdo)
{
    $result = $pdo->exec('DELETE FROM bans WHERE expire_time < UNIX_TIMESTAMP(NOW() - INTERVAL 1 YEAR)');

    if ($result === false) {
        throw new Exception('Could not delete old bans.');
    }

    return $result;
}
