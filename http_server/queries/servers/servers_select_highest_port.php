<?php

function servers_select_highest_port($pdo)
{
    $stmt = $pdo->prepare('
        SELECT servers.port
        FROM servers
        ORDER BY servers.port DESC
        LIMIT 1
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select highest server port.');
    }

    $server = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$server) {
        return 0;
    }

    return $server->port;
}
