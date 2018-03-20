<?php

function friend_select($pdo, $user_id, $friend_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM friends
        WHERE user_id = p_user_id
        AND friend_id = p_target_id
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $friend_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch friend');
    }

    $friend = $stmt->fetch(PDO::FETCH_OBJ);
    return $friend;
}
