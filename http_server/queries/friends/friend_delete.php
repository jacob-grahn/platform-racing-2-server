<?php

function friend_delete($pdo, $user_id, $friend_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM friends
         WHERE user_id = :user_id
           AND friend_id = :friend_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $friend_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not remove this user from your friends list.');
    }

    return $result;
}
