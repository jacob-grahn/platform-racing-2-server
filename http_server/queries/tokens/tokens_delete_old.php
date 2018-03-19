<?php

function tokens_delete_old($pdo)
{
    $result = $pdo->exec('DELETE FROM tokens WHERE Date(time) < date_sub(NOW(), interval 1 month)');

    if ($result === false) {
        throw new Exception('Could not delete old login tokens');
    }

    return $result;
}
