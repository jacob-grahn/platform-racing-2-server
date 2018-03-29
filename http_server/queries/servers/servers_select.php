<?php

function servers_select($pdo)
{
    $result = $pdo->exec('
        SELECT *
          FROM servers
         WHERE active = 1
         ORDER BY server_id ASC
    ');

    if ($result === false) {
        throw new Exception('Could not select active servers.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
