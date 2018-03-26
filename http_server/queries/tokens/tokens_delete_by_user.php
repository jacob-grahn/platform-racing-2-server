<?php

function tokens_delete_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM tokens
        WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not delete this user\'s login tokens.');
    }

    return $result;
}
