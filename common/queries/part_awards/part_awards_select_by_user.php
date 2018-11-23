<?php

function part_awards_select_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT type, part
          FROM part_awards
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select user\'s pending awards.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
