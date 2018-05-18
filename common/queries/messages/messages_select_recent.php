<?php

// TO-DO: is this needed?
function messages_select_recent($pdo, $min_message_id)
{
    $stmt = $pdo->prepare('
        SELECT to_user_id, message_id
          FROM messages
         WHERE message_id > :min_message_id
      ORDER BY message_id ASC
         LIMIT 50
    ');
    $stmt->bindValue(':min_message_id', $min_message_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select recent messages.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
