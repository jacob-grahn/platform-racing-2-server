<?php

function part_awards_insert($pdo, $user_id, $type, $part)
{
    $stmt = $pdo->prepare('
        INSERT INTO part_awards
           SET user_id = :user_id,
               type = :type,
               part = :part,
               dateline = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':part', $part, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not insert part award.');
    }

    return $result;
}
