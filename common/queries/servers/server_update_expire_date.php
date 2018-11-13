<?php

function server_update_expire_date($pdo, $expire_time, $server_id)
{
    $stmt = $pdo->prepare('
        UPDATE servers
           SET expire_date = FROM_UNIXTIME(:expire_time)
         WHERE server_id = :server_id
    ');
    $stmt->bindValue(':expire_time', $expire_time, PDO::PARAM_INT);
    $stmt->bindValue(':server_id', $server_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update server expire date.');
    }

    return $result;
}
