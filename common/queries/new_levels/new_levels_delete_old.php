<?php

function new_levels_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM pr2_new_levels
              WHERE time < UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)
    ');

    if ($result === false) {
        throw new Exception('Could not delete levels older than a day from newest.');
    }

    return $result;
}
