<?php

function name_to_id($pdo, $user_name)
{
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE name = :user_name LIMIT 1');
    $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($result === false) {
        throw new Exception('Could not find a user with that name.');
    }
    
    return $result;
}
    
?>
