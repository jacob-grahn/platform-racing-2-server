<?php

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
