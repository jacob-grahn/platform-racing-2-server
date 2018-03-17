<?php

function pr2_select($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('SELECT * FROM pr2 WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query to select all from pr2');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);
    if ($row === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not find a pr2 row for this user');
        }
        else {
            return false;
        }
    }

    return $row;
}
