<?php

function messages_reported_insert(
    $pdo,
    $to_user_id,
    $from_user_id,
    $reporter_ip,
    $from_ip,
    $sent_time,
    $reported_time,
    $message_id,
    $message
) {
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
    $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':from_user_id', $from_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':reporter_ip', $reporter_ip, PDO::PARAM_STR);
    $stmt->bindValue(':from_ip', $from_ip, PDO::PARAM_STR);
    $stmt->bindValue(':sent_time', $sent_time, PDO::PARAM_INT);
    $stmt->bindValue(':reported_time', $reported_time, PDO::PARAM_INT);
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not report the message.');
    }

    return $result;
}
