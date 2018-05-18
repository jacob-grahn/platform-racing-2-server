<?php

// TO-DO: is this needed?
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
