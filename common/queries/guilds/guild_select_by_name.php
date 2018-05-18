<?php

function guild_select_by_name($pdo, $guild_name)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM guilds
        WHERE guild_name = :guild_name
        LIMIT 1
    ');
    $stmt->bindValue(':guild_name', $guild_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query guild_select_by_name.');
    }

    $guild = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($guild === false) {
        throw new Exception('Could not find a guild with that name.');
    }

    return $guild;
}
