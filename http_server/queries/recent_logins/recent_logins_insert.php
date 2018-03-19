<?php

function recent_logins_insert($pdo, $user_id, $ip, $country_code)
{
    $stmt = $pdo->prepare('
        INSERT INTO recent_logins
                SET user_id = :user_id,
                    ip = :ip,
                    country_code = :country_code
        ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $count, PDO::PARAM_STR);
    $stmt->bindValue(':country_code', $country_code, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if($result === false) {
        return false;
    }
    
    return true;
}

?>
