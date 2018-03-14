<?php

function users_new_delete_old($pdo)
{
	$result = $pdo->exec('
        DELETE FROM users_new
        WHERE time < UNIX_TIMESTAMP( NOW() - INTERVAL 1 DAY );
    ');

    if ($result === false) {
        throw new Exception('could not delete old users_new');
    }

    return $result;
}
