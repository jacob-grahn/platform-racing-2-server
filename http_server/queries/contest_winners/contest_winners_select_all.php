<?php

function contest_winners_select_all($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT contest_winners.contest_id AS contest_id,
               contest_winners.winner_id AS winner_id,
               contest_winners.win_time AS win_time,
               contest_winners.awarder_ip AS awarder_ip,
               contest_winners.awarded_by AS awarded_by,
               contest_winners.prizes_awarded AS prizes_awarded,
               contest_winners.comment AS comment,
               contests.contest_name AS contest_name
          FROM contest_winners
          LEFT JOIN contests ON contests.contest_id = contest_winners.contest_id
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
