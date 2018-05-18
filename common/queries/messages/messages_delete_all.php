<?php

function messages_delete_all($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
         WHERE to_user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not delete all of your messages.');
    }

    return $result;
}
