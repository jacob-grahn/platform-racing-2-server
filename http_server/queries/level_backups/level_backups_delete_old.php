<?php

function level_backups_delete_old($pdo)
{
    $result = $pdo->exec('DELETE FROM level_backups WHERE date < DATE_SUB(NOW(), INTERVAL 1 MONTH)');

    if ($result === false) {
        throw new Exception('could not delete old level backups');
    }

    return $result;
}
