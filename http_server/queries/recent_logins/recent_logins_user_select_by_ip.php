<?php

function recent_logins_user_select_by_ip($pdo, $ip)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('
        SELECT DISTINCT user_id
          FROM recent_logins
         WHERE ip = :ip
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        return false;
    }
    
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
