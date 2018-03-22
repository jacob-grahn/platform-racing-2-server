<?php

function recent_logins_user_select_by_ip($pdo, $ip, $count = 100)
{
    $count = (int) $count;
    $stmt = $pdo->prepare('SELECT DISTINCT user_id FROM recent_logins WHERE ip = :ip LIMIT 0 , :count');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        return false;
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
