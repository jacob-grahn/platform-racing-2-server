<?php

function server_insert($pdo, $server_name, $address, $port, $expire_date, $salt, $guild_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO servers
           SET server_name = :server_name,
               address = :address,
               port = :port,
               expire_date = FROM_UNIXTIME(:expire_date),
               active = true,
               salt = :salt,
               guild_id = :guild_id
    ');
    $stmt->bindValue(':server_name', $server_name, PDO::PARAM_STR);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    $stmt->bindValue(':port', $port, PDO::PARAM_INT);
    $stmt->bindValue(':expire_date', $expire_date, PDO::PARAM_STR);
    $stmt->bindValue(':salt', $salt, PDO::PARAM_STR);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not create server.');
    }

    return $result;
}
