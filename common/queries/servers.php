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


function server_select_by_guild_id($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM servers
         WHERE guild_id = :guild_id
         LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query server_select_by_guild_id.');
    }
    
    return $stmt->fetch(PDO::FETCH_OBJ);
}


function server_select_by_port($pdo, $port)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM servers
         WHERE port = :port
         LIMIT 1
    ');
    $stmt->bindValue(':port', $port, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query server_select_by_port.');
    }
    
    return $stmt->fetch(PDO::FETCH_OBJ);
}


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


function servers_deactivate_expired($pdo)
{
    $result = $pdo->exec('
        UPDATE servers
           SET active = 0,
               status = "offline"
         WHERE expire_date < NOW()
    ');
    
    if ($result === false) {
        throw new Exception('Could not deactivate expired servers');
    }

    return $result;
}


function servers_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM servers
        WHERE expire_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ');

    if ($result === false) {
        throw new Exception('Could not delete old servers.');
    }

    return $result;
}


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


function servers_select($pdo)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM servers
         WHERE active = 1
         ORDER BY server_id ASC
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select active servers.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
