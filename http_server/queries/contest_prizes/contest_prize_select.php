<?php

function contest_prize_select($pdo, $prize_id)
{
    $stmt = $pdo->prepare('
        SELECT contest_id, part_type, part_id, added
          FROM contest_prizes
         WHERE prize_id = :prize_id
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_type', $part_type, PDO::PARAM_STR);
    $stmt->bindValue(':part_id', $part_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select prize ID.');
    }
    
    $prize = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($prize)) {
        if ($suppress_error == false) {
            throw new Exception("Could not find a prize row for contest #$contest_id, part type \"$part_type\", and part id #$part_id.");
        } else {
            return false;
        }
    }
    
    return $prize;
}
