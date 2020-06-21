<?php


function message_delete($pdo, $user_id, $message_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
         WHERE to_user_id = :to_user_id
           AND message_id = :message_id
         LIMIT 1
    ');
    $stmt->bindValue(':to_user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete message.');
    }

    return $result;
}


function message_insert($pdo, $to_user_id, $from_user_id, $message, $ip, $guild_message = 0)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        INSERT INTO messages
        SET to_user_id = :to_user_id,
            from_user_id = :from_user_id,
            message = :message,
            guild_message = :gm,
            ip = :ip,
            time = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':from_user_id', $from_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $stmt->bindValue(':gm', $guild_message, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not send message.');
    }

    return $result;
}


function message_select($pdo, $message_id, $suppress_error = false)
{
    db_set_encoding($pdo, 'utf8mb4');
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


function messages_delete_all($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
         WHERE to_user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete all of your messages.');
    }

    return $result;
}


function messages_delete_old($pdo)
{
    $stmt = $pdo->prepare('
        DELETE FROM messages
        WHERE time < UNIX_TIMESTAMP(date_sub(NOW(), INTERVAL 2 YEAR))
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete old messages.');
    }

    return $result;
}


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


function messages_select($pdo, $to_user_id, $start, $count)
{
    db_set_encoding($pdo, 'utf8mb4');

    $start = (int) $start;
    $count = (int) $count;

    $stmt = $pdo->prepare('
        SELECT message_id, message, guild_message, time, from_user_id
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
