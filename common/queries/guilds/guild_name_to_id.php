<?php

function guild_name_to_id($pdo, $guild_name, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT guild_id
          FROM guilds
         WHERE guild_name = :guild_name
         LIMIT 1
    ');
    $stmt->bindValue(':guild_name', $guild_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query guild_name_to_id.');
    }
    
    $guild = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($guild)) {
        if ($suppress_error === false) {
            throw new Exception('Could not find a guild with that name.');
        } else {
            return false;
        }
    }
    
    return $guild->guild_id;
}
