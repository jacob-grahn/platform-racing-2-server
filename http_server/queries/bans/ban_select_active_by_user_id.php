<?php

function ban_select_active_by_user_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_user_id = :user_id
        AND account_ban = 1
        AND lifted != 1
        AND expire_time > :time
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query ban_select_active_by_user_id.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
