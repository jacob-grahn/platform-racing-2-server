<?php

function pr2_update_part_array($pdo, $user_id, $type, $part_array)
{
    $stmt = $pdo->prepare('
        IF (p_type = "hat") THEN
            UPDATE pr2
            SET hat_array = p_part_array
            WHERE user_id = p_user_id;

        ELSEIF (p_type = "head") THEN
            UPDATE pr2
            SET head_array = p_part_array
            WHERE user_id = p_user_id;

        ELSEIF (p_type = "body") THEN
            UPDATE pr2
            SET body_array = p_part_array
            WHERE user_id = p_user_id;

        ELSEIF (p_type = "feet") THEN
            UPDATE pr2
            SET feet_array = p_part_array
            WHERE user_id = p_user_id;

        END IF;
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':part_array', $part_array, PDO::PARAM_STR);
    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update pr2 part array');
    }

    return $result;
}
