<?php

function message_select($pdo, $message_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM messages
         WHERE message_id = :message_id
         LIMIT 1
    ');
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query message_select.');
    }

    $message = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($message)) {
        if ($suppress_error === false) {
            throw new Exception('Could not find a message with that ID.');
        } else {
            return false;
        }
    }

    return $message;
}
