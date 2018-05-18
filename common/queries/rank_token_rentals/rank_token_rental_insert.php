<?php

function rank_token_rental_insert($pdo, $user_id, $guild_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO rank_token_rentals
           SET user_id = :user_id,
               guild_id = :guild_id,
               date = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not start your rank token rental.');
    }

    return $result;
}
