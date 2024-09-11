<?php


function following_delete($pdo, $user_id, $following_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM follows
         WHERE user_id = :user_id
           AND following_id = :following_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':following_id', $following_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not unfollow this user.');
    }

    return $result;
}


function following_insert($pdo, $user_id, $following_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO follows
           SET user_id = :user_id,
               following_id = :following_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':following_id', $following_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not follow this user.');
    }

    return $result;
}


function following_select($pdo, $user_id, $following_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM follows
         WHERE user_id = :user_id
           AND following_id = :following_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':following_id', $following_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not fetch followed user from your following list.');
    }

    $following = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($following) && !$suppress_error) {
        throw new Exception("Could not find user #$following_id on your following list.");
    }

    return $following;
}


function following_select_list($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT u.user_id, u.name, u.power, u.trial_mod, u.ca, u.status, p.rank, p.hat_array,
               rt.used_tokens, f.following_id
          FROM follows f
         INNER JOIN users u ON u.user_id = f.following_id
          LEFT JOIN pr2 p ON u.user_id = p.user_id
          LEFT JOIN rank_tokens rt ON u.user_id = rt.user_id
         WHERE f.user_id = :user_id
         LIMIT 0, 250
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve the list of users you\'re following.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function followers_select($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT f.user_id, u.name
        FROM follows f
        INNER JOIN users u ON u.user_id = f.following_id
        WHERE f.following_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select your followers.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
