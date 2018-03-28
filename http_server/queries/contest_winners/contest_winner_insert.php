<?php

function contest_winner_insert($pdo, $contest_id, $winner_name, $host_ip, $comment)
{
    $stmt = $pdo->prepare('
        INSERT INTO contest_winners
                SET contest_id = :contest_id,
                    winner_name = :winner_name,
                    win_time = :win_time,
                    host_ip = :host_ip,
                    comment = :comment,
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':winner_name', $winner_name, PDO::PARAM_STR);
    $stmt->bindValue(':win_time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':host_ip', $host_ip, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not record winner.');
    }
    
    return true;
}
