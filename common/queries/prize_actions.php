<?php


function prize_action_insert($pdo, $user_id, $message, $type, $prizer, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO prize_actions
           SET time = :time,
               user_id = :user_id,
               message = :message,
               type = :type,
               prizer = :prizer,
               ip = :ip
    ');
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':prizer', (int) $prizer, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record prize action.');
    }

    return $result;
}


function prize_actions_select($pdo, $in_start, $in_count)
{
    $start = max((int) $in_start, 0);
    $count = min(max((int) $in_count, 0), 100);

    $stmt = $pdo->prepare('
          SELECT *
            FROM prize_actions
           ORDER BY time DESC
           LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve the prize action log.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
