<?php

function message_delete($pdo, $user_id, $message_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
         WHERE to_user_id = :to_user_id
           AND message_id = :message_id
         LIMIT 1
    ');
    $stmt->bindValue(':to_user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not delete message.');
    }

    return $result;
}
