<?php

function users_reset_status($pdo)
{
    $stmt = $pdo->prepare('UPDATE users SET status = "offline" WHERE time < UNIX_TIMESTAMP( NOW() - INTERVAL 1 DAY )');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Error resetting user status');
    }

    return $result;
}
