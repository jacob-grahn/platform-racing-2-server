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
        SELECT u.user_id, u.name, u.power, u.trial_mod, u.ca, u.status, p.rank, p.hat_array,
               rt.used_tokens, f.friend_id
          FROM friends f
         INNER JOIN users u ON u.user_id = f.friend_id
          LEFT JOIN pr2 p ON u.user_id = p.user_id
          LEFT JOIN rank_tokens rt ON u.user_id = rt.user_id
         WHERE f.user_id = :user_id
         LIMIT 0, 250
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select your friends.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
