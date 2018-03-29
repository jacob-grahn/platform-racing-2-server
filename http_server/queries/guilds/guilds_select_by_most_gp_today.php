<?php

function guilds_select_by_most_gp_today($pdo)
{
    $stmt = $pdo->prepare('
        SELECT guild_id, guild_name, gp_today, gp_total
          FROM guilds
         WHERE member_count > 0
            OR gp_today > 0
            OR gp_total > 100
         ORDER BY gp_today DESC, gp_total DESC
         LIMIT 50
    ');
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query guilds_select_by_most_gp_today.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
