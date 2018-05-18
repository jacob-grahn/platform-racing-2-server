<?php

function messages_select($pdo, $to_user_id, $start, $count)
{
    $start = (int) $start;
    $count = (int) $count;
    
    $stmt = $pdo->prepare('
        SELECT message_id, message, time, from_user_id
          FROM messages
         WHERE to_user_id = :to_user_id
         ORDER BY time desc
         LIMIT :start, :count
    ');
    $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not retrieve your private messages.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
