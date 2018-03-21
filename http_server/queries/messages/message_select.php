<?php

function message_select($pdo, $message_id)
{
    $stmt = $pdo->prepare('
        SELECT * FROM messages
        WHERE message_id = :message_id
        LIMIT 1
    ');
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('could not select message');
    }

    $message = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$message) {
        throw new Exception('Message not found.');
    }

    return $message;
}
