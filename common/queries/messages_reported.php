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


function messages_reported_insert($pdo, $to_id, $from_id, $reporter_ip, $from_ip, $sent, $reported, $mid, $m)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        INSERT INTO messages_reported
           SET to_user_id = :to_user_id,
               from_user_id = :from_user_id,
               reporter_ip = :reporter_ip,
               from_ip = :from_ip,
               sent_time = :sent_time,
               reported_time = :reported_time,
               message_id = :message_id,
               message = :message
    ');
    $stmt->bindValue(':to_user_id', $to_id, PDO::PARAM_INT);
    $stmt->bindValue(':from_user_id', $from_id, PDO::PARAM_INT);
    $stmt->bindValue(':reporter_ip', $reporter_ip, PDO::PARAM_STR);
    $stmt->bindValue(':from_ip', $from_ip, PDO::PARAM_STR);
    $stmt->bindValue(':sent_time', $sent, PDO::PARAM_INT);
    $stmt->bindValue(':reported_time', $reported, PDO::PARAM_INT);
    $stmt->bindValue(':message_id', $mid, PDO::PARAM_INT);
    $stmt->bindValue(':message', $m, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not report the message.');
    }

    return $result;
}


function messages_reported_select($pdo, $start, $count)
{
    db_set_encoding($pdo, 'utf8mb4');
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
