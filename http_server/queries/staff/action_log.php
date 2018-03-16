<?php

function log_mod_action($pdo, $string, $user_id, $ip)
{
    $stmt = $pdo->prepare('INSERT INTO mod_action (string, user_id, ip, time) VALUES (:string, :user_id, :ip, :time)');
    $stmt->bindValue(':string', $string, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not log mod action');
    }

    return $result;
}

function log_admin_action($pdo, $string, $user_id, $ip)
{
    $stmt = $pdo->prepare('INSERT INTO admin_action (string, user_id, ip, time) VALUES (:string, :user_id, :ip, :time)');
    $stmt->bindValue(':string', $string, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not log admin action');
    }

    return $result;
}
