<?php

function guild_update($pdo, $guild_id, $guild_name, $emblem, $note, $owner_id)
{
    $stmt = $pdo->prepare('
        UPDATE guilds
           SET guild_name = :name,
               emblem = :emblem,
               note = :note,
               owner_id = :owner_id
         WHERE guild_id = :guild_id
         LIMIT 1
    ');
    $stmt->bindValue(':name', $guild_name, PDO::PARAM_STR);
    $stmt->bindValue(':emblem', $emblem, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not update guild.');
    }
    
    return $result;
}
