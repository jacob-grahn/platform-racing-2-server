<?php

function prize_insert($pdo, $contest_id, $prize_type, $prize_id)
{
    $prize_type = strtolower($prize_type);

    $stmt = $pdo->prepare('
        INSERT INTO contest_prizes
                SET contest_id = :contest_id,
                    prize_type = :type,
                    prize_id = :prize_id,
                    added = :added
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $prize_type, PDO::PARAM_STR);
    $stmt->bindValue(':prize_id', $prize_id, PDO::PARAM_INT);
    $stmt->bindValue(':added', time(), PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not add prize to contest $contest_id.");
    }
    
    return true;
}
