<?php

function gp_increment($pdo, $user_id, $guild_id, $gp)
{
    $stmt = $pdo->prepare('
        INSERT INTO gp
           SET user_id = :user_id,
               guild_id = :guild_id,
               gp_today = :gp,
               gp_total = :gp
        ON DUPLICATE KEY UPDATE
               gp_today = gp_today + :gp,
               gp_total = gp_total + :gp
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':gp', $gp, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record your earned GP.');
    }

    return $result;
}
