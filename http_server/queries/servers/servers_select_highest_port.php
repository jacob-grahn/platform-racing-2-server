<?php

function servers_select_highest_port($pdo)
{
    $result = $pdo->exec('
        SELECT port
          FROM servers
         LIMIT 1
    ');

    if ($result === false) {
        throw new Exception('Could not select the highest server port.');
    }

    $server = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($server)) {
        return 0;
    }

    return $server->port;
}
