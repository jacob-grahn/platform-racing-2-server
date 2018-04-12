<?php

function epic_upgrades_upsert($pdo, $user_id, $epic_hats, $epic_heads, $epic_bodies, $epic_feet)
{
    $stmt = $pdo->prepare('
        INSERT INTO epic_upgrades
        SET user_id = :user_id,
    		epic_hats = :epic_hats,
    		epic_heads = :epic_heads,
    		epic_bodies = :epic_bodies,
    		epic_feet = :epic_feet
        ON DUPLICATE KEY UPDATE
    		epic_hats = :epic_hats,
    		epic_heads = :epic_heads,
    		epic_bodies = :epic_bodies,
    		epic_feet = :epic_feet
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':epic_hats', $epic_hats, PDO::PARAM_STR);
    $stmt->bindValue(':epic_heads', $epic_heads, PDO::PARAM_STR);
    $stmt->bindValue(':epic_bodies', $epic_bodies, PDO::PARAM_STR);
    $stmt->bindValue(':epic_feet', $epic_feet, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not upsert into epic_upgrades');
    }

    return $result;
}
