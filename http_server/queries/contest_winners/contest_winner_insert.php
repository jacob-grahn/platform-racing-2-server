<?php

function contest_winner_insert($pdo, $contest_id, $winner_id, $host_ip, $awarded_by, $comment)
{
    $stmt = $pdo->prepare('
        INSERT INTO contest_winners
                SET contest_id = :contest_id,
                    winner_id = :winner_id,
                    win_time = :win_time,
                    host_ip = :host_ip,
                    awarded_by = :awarded_by,
                    comment = :comment
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':winner_id', $winner_id, PDO::PARAM_INT);
    $stmt->bindValue(':win_time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':host_ip', $host_ip, PDO::PARAM_STR);
    $stmt->bindValue(':awarded_by', $awarded_by, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not record winner.');
    }
    
    return true;
}
