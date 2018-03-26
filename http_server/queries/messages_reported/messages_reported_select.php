<?php

function messages_reported_select($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT messages_reported.*, u1.name as from_name, u2.name as to_name
          FROM messages_reported, users u1, users u2
         WHERE to_user_id = u2.user_id
           AND from_user_id = u1.user_id
         ORDER BY reported_time desc
         LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not fetch the list of reported messages.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
