<?php

function bans_select_by_user_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_user_id = :user_id
        AND account_ban = 1
        ORDER BY time DESC
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select bans by user ID.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
