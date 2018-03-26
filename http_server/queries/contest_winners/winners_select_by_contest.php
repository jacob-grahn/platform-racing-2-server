<?php

function winners_select_by_contest($pdo, $contest_id)
{
    $stmt = $pdo->prepare('
        SELECT pr2_name, time
        FROM contest_winners
        WHERE contest_id = :contest_id
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contest winners.');
    }
    
    $winners = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($winners)) {
        throw new Exception('No winners found for this contest. :(');
    }
    
    return $winners;
}
