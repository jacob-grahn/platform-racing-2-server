<?php

function server_update_expire_date($pdo, $server_id, $expire_timestamp, $server_name)
{
    $expire_date = date("Y-m-d H:i:s", $expire_timestamp);
    $stmt = $pdo->prepare('
        UPDATE servers
           SET expire_date = :expire_date,
               server_name = :server_name,
               active = 1
         WHERE server_id = :server_id
    ');
    $stmt->bindValue(':server_id', $server_id, PDO::PARAM_INT);
    $stmt->bindValue(':server_name', $server_name, PDO::PARAM_STR);
    $stmt->bindValue(':expire_date', $expire_date, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update server expire date.');
    }

    return $result;
}
