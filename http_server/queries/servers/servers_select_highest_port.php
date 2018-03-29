<?php

function servers_select_highest_port($pdo)
{
    $stmt = $pdo->prepare('
        SELECT port
          FROM servers
         ORDER BY port DESC
         LIMIT 1
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select the highest server port.');
    }

    return (int) $stmt->fetchColumn();
}
