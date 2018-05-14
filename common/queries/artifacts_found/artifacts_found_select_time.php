<?php

function artifacts_found_select_time($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT UNIX_TIMESTAMP(time) as timestamp
        FROM artifacts_found
        WHERE user_id = :user_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select your last found artifact.');
    }

    return (int) $stmt->fetchColumn();
}
