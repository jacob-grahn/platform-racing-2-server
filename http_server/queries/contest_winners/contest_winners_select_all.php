<?php

function contest_winners_select_all($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT contest_id, winner_name, win_time
        FROM contest_winners
        ORDER BY win_time DESC
        LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select all contest winners.');
    }
    
    $winners = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($winners)) {
        throw new Exception('No one has won any contests. :(');
    }
    
    return $winners;
}
