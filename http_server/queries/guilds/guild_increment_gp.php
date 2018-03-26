<?php

function guild_increment_gp($pdo, $guild_id, $gp)
{
    $stmt = $pdo->prepare('
        UPDATE guilds
           SET gp_today = gp_today + :gp,
               gp_total = gp_total + :gp,
               active_date = NOW()
         WHERE guild_id = :guild_id
         LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':gp', $gp, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record earned GP for guild #$guild_id.');
    }

    return $result;
}
