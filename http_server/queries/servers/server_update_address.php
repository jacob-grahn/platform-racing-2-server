<?php

function server_update_address($pdo, $server_id, $address)
{
    $stmt = $pdo->prepare('
        UPDATE servers
           SET address = :address
         WHERE server_id = :server_id
         LIMIT 1
    ');
    $stmt->bindValue(':server_id', $server_id, PDO::PARAM_INT);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update server address.');
    }

    return $result;
}
