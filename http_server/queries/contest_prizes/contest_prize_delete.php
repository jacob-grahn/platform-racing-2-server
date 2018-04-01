<?php

function contest_prize_delete($pdo, $prize_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        DELETE FROM contest_prizes
        WHERE prize_id = :prize_id
    ');
    $stmt->bindValue(':prize_id', $prize_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        if ($suppress_error == false) {
            throw new Exception('Could not delete prize.');
        } else {
            return false;
        }
    }
    
    return true;
}
