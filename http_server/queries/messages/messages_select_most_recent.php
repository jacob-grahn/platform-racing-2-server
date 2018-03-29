<?php

function messages_select_most_recent($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT message_id
          FROM messages
         WHERE to_user_id = :user_id
         ORDER BY time DESC
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select the ID of your most recent message.');
    }

    $message = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($message)) {
        return 0;
    }

    return $message->message_id;
}
