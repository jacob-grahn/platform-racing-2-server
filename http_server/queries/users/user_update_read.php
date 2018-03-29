<?php

function user_update_read($pdo, $user_id, $read_message_id)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET read_message_id = :read_message_id
         WHERE user_id = :user_id
           AND read_message_id < :read_message_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':read_message_id', $read_message_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update the last read message ID for user #$user_id.");
    }

    return $result;
}
