<?php

function users_select_by_ip_expanded($pdo, $search_ip, $start = 0, $count = 25)
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
        ORDER BY
          u.time DESC
        LIMIT
          :start, :count
    ");
    $stmt->bindValue(':search_ip', $search_ip, PDO::PARAM_STR);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query users_select_by_ip_expanded.');
    }

    $users = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($users)) {
        $search_ip = htmlspecialchars($search_ip);
        throw new Exception("Could not find any users associated for that IP "
            ."address ($search_ip) with those search parameters.");
    }

    return $users;
}
