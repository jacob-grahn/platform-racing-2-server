<?php

function throttle_awards($pdo, $contest_id, $host_id)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
          FROM contest_winners
         WHERE contest_id = :contest_id
           AND awarded_by = :host_id
           AND win_time > UNIX_TIMESTAMP(NOW() - INTERVAL 6 DAY)
    ");
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not perform query throttle_awards.");
    }
    
    return (int) $stmt->fetchColumn();
}
