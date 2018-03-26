<?php

function recent_logins_insert($pdo, $user_id, $ip, $country_code)
{
    $stmt = $pdo->prepare('
        INSERT INTO recent_logins
          SET user_id = :user_id,
              ip = :ip,
              country = :country_code,
              date = NOW()
        ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':country_code', $country_code, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        return false;
    }
    
    return true;
}
