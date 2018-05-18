<?php

function users_new_insert($pdo, $name, $ip, $time)
{
    $stmt = $pdo->prepare('
        INSERT INTO users_new
           SET user_name = :name,
               ip = :ip,
               time = :time
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':time', $time, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not insert new user into users_new.');
    }
    
    return true;
}
