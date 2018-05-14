<?php

function messages_reported_archive($pdo, $message_id)
{
    $stmt = $pdo->prepare('
        UPDATE messages_reported
           SET archived = 1
         WHERE message_id = :message_id
         LIMIT 1
    ');
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not archive the report of message #$message_id.");
    }

    return $result;
}
