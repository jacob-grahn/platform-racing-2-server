<?php

function friend_insert($pdo, $user_id, $friend_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO friends
           SET user_id = :user_id,
               friend_id = :friend_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $friend_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not add this user to your friends list.');
    }

    return $result;
}
