<?php

function artifact_location_update_first_finder($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
        SET first_finder = :user_id
        WHERE artifact_id = 1
        AND first_finder = 0
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update artifact first finder.');
    }

    return $result;
}
