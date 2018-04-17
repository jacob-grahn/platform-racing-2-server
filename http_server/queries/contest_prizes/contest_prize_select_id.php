<?php

function contest_prize_select_id($pdo, $contest_id, $part_type, $part_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT prize_id
          FROM contest_prizes
         WHERE contest_id = :contest_id
           AND part_type = :part_type
           AND part_id = :part_id
         LIMIT 1
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_type', $part_type, PDO::PARAM_STR);
    $stmt->bindValue(':part_id', $part_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select prize ID.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        if ($suppress_error == false) {
            throw new Exception("Could not find a prize row for contest "
                ."#$contest_id, part type \"$part_type\", and part id #$part_id.");
        } else {
            return false;
        }
    }

    return $row->prize_id;
}
