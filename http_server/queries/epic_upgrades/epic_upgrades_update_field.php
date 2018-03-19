<?php

function epic_upgrades_update_field($pdo, $user_id, $type, $part_array)
{
    $stmt = $pdo->prepare('
        IF (:type = "eHat") THEN
            INSERT INTO epic_upgrades
            SET user_id = :user_id,
                    epic_hats = :part_array
            ON DUPLICATE KEY UPDATE
                    epic_hats = :part_array;

        ELSEIF (:type = "eHead") THEN
            INSERT INTO epic_upgrades
            SET user_id = :user_id,
                    epic_heads = :part_array
            ON DUPLICATE KEY UPDATE
                    epic_heads = :part_array;

        ELSEIF (:type = "eBody") THEN
            INSERT INTO epic_upgrades
            SET user_id = :user_id,
                    epic_bodies = :part_array
            ON DUPLICATE KEY UPDATE
                    epic_bodies = :part_array;

        ELSEIF (:type = "eFeet") THEN
            INSERT INTO epic_upgrades
            SET user_id = :user_id,
                    epic_feet = :part_array
            ON DUPLICATE KEY UPDATE
                    epic_feet = :part_array;

        END IF;
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':part_array', $part_array, PDO::PARAM_STR);
    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update epic_upgrades');
    }

    return $result;
}
