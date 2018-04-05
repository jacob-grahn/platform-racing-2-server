<?php

function users_select_by_ip_expanded($pdo, $search_ip)
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT
          u.name AS 'name',
          u.power AS 'power',
          u.time AS 'time'
        FROM
          users u
          LEFT JOIN recent_logins rl ON u.user_id = rl.user_id
        WHERE
          :search_ip IN (u.ip, u.register_ip, rl.ip)
    ");
    $stmt->bindValue(':search_ip', $search_ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query users_select_by_ip_expanded.');
    }
    
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($users)) {
        $search_ip = htmlspecialchars($search_ip);
        throw new Exception("Could not find any users associated with that IP address ($search_ip).");
    }
    
    return $users;
}
