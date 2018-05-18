<?php

function level_backups_select($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM level_backups
        WHERE user_id = :user_id
        ORDER BY date DESC
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could fetch level backups for user');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
