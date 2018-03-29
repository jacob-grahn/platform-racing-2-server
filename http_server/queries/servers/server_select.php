<?php

function server_select($pdo, $server_id)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM servers
         WHERE server_id = :server_id
         LIMIT 1
    ');
    $stmt->bindValue(':server_id', $server_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select server.');
    }

    $server = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($server)) {
        throw new Exception('Could not find a server with that ID.');
    }

    return $server;
}
