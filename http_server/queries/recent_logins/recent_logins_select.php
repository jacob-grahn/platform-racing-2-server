<?php

function recent_logins_select($pdo, $user_id, $count = 100)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('SELECT * FROM recent_logins WHERE user_id = :user_id LIMIT 0 , :count');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if($result === false) {
        return false;
    } else {
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }   
}
