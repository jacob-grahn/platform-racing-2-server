<?php

function messages_select($pdo, $to_user_id, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT messages.message_id, messages.message, messages.time, messages.from_user_id
        FROM messages
        WHERE messages.to_user_id = :to_user_id
        ORDER BY messages.time desc
        LIMIT :start, :count
    ');
    $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);

    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('could not select messages');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
