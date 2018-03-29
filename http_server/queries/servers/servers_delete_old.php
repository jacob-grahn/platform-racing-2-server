<?php

function servers_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM servers
        WHERE expire_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ');

    if ($result === false) {
        throw new Exception('Could not delete old servers.');
    }

    return $result;
}
