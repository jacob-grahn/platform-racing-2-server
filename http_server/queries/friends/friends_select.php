<?php

function friends_select($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT users.name, users.power, users.status, pr2.rank, pr2.hat_array,
               rank_tokens.used_tokens, friends.friend_id
          FROM friends
         INNER JOIN users ON users.user_id = friends.friend_id
          LEFT JOIN pr2 ON users.user_id = pr2.user_id
          LEFT JOIN rank_tokens ON users.user_id = rank_tokens.user_id
         WHERE friends.user_id = :user_id
         LIMIT 0, 250
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select your friends.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
