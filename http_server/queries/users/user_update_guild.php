<?php

function user_update_guild($pdo, $user_id, $guild_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET guild = :guild_id
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update the guild of user #$user_id.");
    }

    return $result;
}
