<?php

function servers_select($pdo)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM servers
         WHERE active = 1
         ORDER BY server_id ASC
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select active servers.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
