<?php

function message_select($pdo, $message_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT * FROM messages
        WHERE message_id = :message_id
        LIMIT 1
    ');
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('Could not perform query to select this message from the database.');
    }

    $message = $stmt->fetch(PDO::FETCH_OBJ);
    if ($message === false) {
        if ($suppress_error === false) {
            throw new Exception('Message not found.');
        } else {
            return false;
        }
    }

    return $message;
}
