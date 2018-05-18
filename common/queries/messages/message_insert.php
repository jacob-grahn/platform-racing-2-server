<?php

function message_insert($pdo, $to_user_id, $from_user_id, $message, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO messages
        SET to_user_id = :to_user_id,
            from_user_id = :from_user_id,
            message = :message,
            ip = :ip,
            time = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':from_user_id', $from_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not send message.');
    }

    return $result;
}
