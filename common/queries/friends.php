<?php


function friend_delete($pdo, $user_id, $friend_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM friends
         WHERE user_id = :user_id
           AND friend_id = :friend_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $friend_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not remove this user from your friends list.');
    }

    return $result;
}


function friend_insert($pdo, $user_id, $friend_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO friends
           SET user_id = :user_id,
               friend_id = :friend_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $friend_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not add this user to your friends list.');
    }

    return $result;
}


function friend_select($pdo, $user_id, $friend_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM friends
         WHERE user_id = :user_id
           AND friend_id = :friend_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':friend_id', $friend_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not fetch friend from your friends list.');
    }

    $friend = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($friend) && !$suppress_error) {
        throw new Exception("Could not find user #$friend_id on your friends list.");
    }

    return $friend;
}


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
