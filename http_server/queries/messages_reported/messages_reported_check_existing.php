<?php

function messages_reported_check_existing($pdo, $message_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM messages_reported
         WHERE message_id = :message_id
    ');
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not check if this message has been reported already.");
    }
    
    return (bool) $stmt->fetchColumn();
}
