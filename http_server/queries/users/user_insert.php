<?php

function user_insert($pdo, $name, $pass_hash, $ip, $time, $email)
{
    $stmt = $pdo->prepare('
        INSERT INTO users
        SET name = :name,
            pass_hash = :pass_hash,
            register_ip = :ip,
            ip = :ip,
            time = :time,
            register_time = :time,
            email = :email;

        INSERT INTO users_new
        SET user_name = :name,
            ip = :ip,
            time = :time;
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':pass_hash', $pass_hash, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('Could not insert new user.');
    }

    return $result;
}
