<?php

function users_select_by_ip($pdo, $ip)
{
    $stmt = $pdo->prepare('
          SELECT user_id, name, time, power
            FROM users
           WHERE ip = :ip
        GROUP BY user_id
        ORDER BY time DESC
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not search for users with that IP.');
    }
    
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($users)) {
        throw new Exception('Could not find any users with that IP.');
    }
    
    return $users;
}
