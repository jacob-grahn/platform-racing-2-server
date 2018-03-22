<?php

function folding_select_by_user_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('SELECT * FROM folding_at_home WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select a user from folding_at_home');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if ($row === false) {
        throw new Exception('Could not find folding_at_home row');
    }

    return $row;
}
