<?php


function ignored_delete($pdo, $user_id, $ignore_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM ignored
         WHERE user_id = :user_id
           AND ignore_id = :ignore_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ignore_id', $ignore_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not remove this user from your ignored players list.');
    }

    return $result;
}


function ignored_insert($pdo, $user_id, $ignore_id)
{
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO ignored
               SET user_id = :user_id,
                   ignore_id = :ignore_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ignore_id', $ignore_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not add this user to your ignored players list.');
    }

    return $result;
}


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


function ignored_select($pdo, $user_id, $ignore_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM ignored
         WHERE ignore_id = :ignore_id
           AND user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ignore_id', $ignore_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select an ignored user.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row) && !$suppress_error) {
        throw new Exception("Could not find user #$ignore_id on your ignored list.");
    }

    return $row;
}
