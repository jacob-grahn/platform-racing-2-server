<?php

function ignored_select_list($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT users.name, users.power, users.status, pr2.rank, pr2.hat_array,
               rank_tokens.used_tokens, ignored.ignore_id
          FROM ignored
         INNER JOIN users ON users.user_id = ignored.ignore_id
          LEFT JOIN pr2 ON users.user_id = pr2.user_id
          LEFT JOIN rank_tokens ON users.user_id = rank_tokens.user_id
         WHERE ignored.user_id = :user_id
         LIMIT 0, 250
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select your ignored list.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
