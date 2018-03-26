<?php

function prize_row_select($pdo, $contest_id, $prize_type, $prize_id)
{
    $stmt = $pdo->prepare('
        SELECT row_id
        WHERE contest_id = :contest_id
        AND prize_type = :prize_type
        AND prize_id = :prize_id
        LIMIT 1
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':prize_type', $prize_type, PDO::PARAM_STR);
    $stmt->bindValue(':prize_id', $prize_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select prize row.');
    }
    
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (empty($row)) {
        throw new Exception("Could not find a prize row for contest #$contest_id, prize type \"$prize_type\", and prize #$prize_id.");
    }
    
    return $row->row_id;
}
