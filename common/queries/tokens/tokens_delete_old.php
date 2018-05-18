<?php

function tokens_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM tokens
        WHERE Date(time) < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ');

    if ($result === false) {
        throw new Exception('Could not delete expired login tokens.');
    }

    return $result;
}
