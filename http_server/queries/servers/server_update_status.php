<?php

function server_update_status($pdo, $server_id, $status, $population, $happy_hour)
{
    $stmt = $pdo->prepare('
        UPDATE servers
           SET status = :status,
               population = :population,
               happy_hour = :happy_hour
         WHERE server_id = :server_id
         LIMIT 1
    ');
    $stmt->bindValue(':server_id', $server_id, PDO::PARAM_INT);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':population', $population, PDO::PARAM_INT);
    $stmt->bindValue(':happy_hour', $happy_hour, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update server status.');
    }

    return $result;
}
