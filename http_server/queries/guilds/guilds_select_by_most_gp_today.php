<?php

function guilds_select_by_most_gp_today($pdo)
{
    $stmt = $pdo->prepare('
        SELECT guild_id, guild_name, gp_today
        FROM guilds
        ORDER BY gp_today DESC
        LIMIT 50
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch top guilds');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
