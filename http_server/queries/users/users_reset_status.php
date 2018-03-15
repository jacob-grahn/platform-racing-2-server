<?php

function users_reset_status($pdo)
{
	$stmt = $pdo->prepare('
        UPDATE users
        SET status = "offline"
        WHERE time < UNIX_TIMESTAMP( NOW() - INTERVAL 3 DAY )
    ');
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('error resetting user status');
    }

    return $result;
}
