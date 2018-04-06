<?php

function users_count_from_ip_expanded($pdo, $search_ip)
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT
          COUNT(u.*) as 'count',
        FROM
          users u
          LEFT JOIN recent_logins rl ON u.user_id = rl.user_id
        WHERE
          :search_ip IN (u.ip, u.register_ip, rl.ip)
    ");
    $stmt->bindValue(':search_ip', $search_ip, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query users_count_from_ip_expanded.');
    }
    
    return (int) $stmt->fetchColumn();
}
