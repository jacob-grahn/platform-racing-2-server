<?php

function pr2_insert($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO pr2
           SET user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query to insert PR2 player data.');
    }

    return $result;
}
