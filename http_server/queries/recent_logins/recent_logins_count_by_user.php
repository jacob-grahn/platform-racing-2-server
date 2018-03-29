<?php

function recent_logins_count_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM recent_logins
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not check the total number of logins for this user.");
    }
    
    $count = $stmt->fetchColumn();
    
    if ((int) $count == 0 || $count == false || empty($count)) {
        return 0;
    }
    
    return $count;
}
