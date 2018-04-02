<?php

function users_select_by_ip($pdo, $ip)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('
          SELECT user_id
            FROM users
           WHERE ip = :ip
              OR register_ip = :ip
        ORDER BY active_date DESC
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
