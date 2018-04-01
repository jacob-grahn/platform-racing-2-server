<?php

function contest_prize_insert($pdo, $contest_id, $part_type, $part_id)
{
    $part_type = strtolower($part_type);

    $stmt = $pdo->prepare('
        INSERT INTO contest_prizes
                SET contest_id = :contest_id,
                    part_type = :part_type,
                    part_id = :part_id,
                    added = :added
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_type', $part_type, PDO::PARAM_STR);
    $stmt->bindValue(':part_id', $part_id, PDO::PARAM_INT);
    $stmt->bindValue(':added', time(), PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not add prize to contest #$contest_id.");
    }
    
    // return last insert id
    return $pdo->lastInsertId();
}
