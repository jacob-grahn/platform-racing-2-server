<?php

function rank_token_rentals_count($pdo, $user_id, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*) AS count
          FROM rank_token_rentals
         WHERE (guild_id = :guild_id AND guild_id != 0)
            OR user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not count how many rank tokens you are currently renting.');
    }

    return (int) $stmt->fetchColumn();
}
