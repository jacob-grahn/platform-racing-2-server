<?php

function server_insert($pdo, $expire_time, $server_name, $address, $port, $guild_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO servers
           SET expire_date = FROM_UNIXTIME(:expire_time),
               server_name = :server_name,
               address = :address,
               port = :port,
               guild_id = :guild_id
    ');
    $stmt->bindValue(':expire_time', $expire_time, PDO::PARAM_INT);
    $stmt->bindValue(':server_name', $server_name, PDO::PARAM_STR);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    $stmt->bindValue(':port', $port, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not create server.');
    }

    return $result;
}
