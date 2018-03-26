<?php

function contest_winner_insert($pdo, $contest_id, $winner_name)
{
    $stmt = $pdo->prepare('
        INSERT INTO contest_winners
                SET contest_id = :contest_id,
                    winner_name = :winner_name,
                    win_time = :win_time,
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':winner_name', $winner_name, PDO::PARAM_STR);
    $stmt->bindValue(':win_time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not record winner.');
    }
    
    return true;
}
