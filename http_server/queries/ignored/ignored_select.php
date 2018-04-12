<?php

// TO-DO: Is this needed?
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
