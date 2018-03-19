<?php

function bans_select_by_ip($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM bans
        WHERE banned_user_id = :user_id
        AND account_ban = 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('could not select bans by user_id');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
