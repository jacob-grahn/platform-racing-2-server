<?php

function guild_delete($pdo, $guild_id)
{
    guild_delete_from_guilds($pdo, $guild_id);
    guild_delete_from_users($pdo, $guild_id);
    guild_delete_from_gp($pdo, $guild_id);
}

function guild_delete_from_guilds($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM guilds
         WHERE guild_id = :guild_id
         LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not perform query guild_delete_from_guilds.");
    }

    return $result;
}

function guild_delete_from_users($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
        SET guild = 0
        WHERE guild = :guild_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not perform query guild_delete_from_users.");
    }

    return $result;
}

function guild_delete_from_gp($pdo, $guild_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM gp
        WHERE guild_id = :guild_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query guild_delete_from_gp.');
    }

    return $result;
}
