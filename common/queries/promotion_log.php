<?php


function promotion_log_count($pdo, $min_time)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM promotion_log
         WHERE power > 1
           AND time > :min_time
    ');
    $stmt->bindValue(':min_time', $min_time, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query promotion_log_count.');
    }

    return (int) $stmt->fetchColumn();
}


function promotion_log_insert($pdo, $message, $time)
{
    $stmt = $pdo->prepare('
        INSERT INTO promotion_log
           SET message = :message,
               power = 2,
               time = :time
    ');
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query promotion_log_insert.');
    }

    return $result;
}
