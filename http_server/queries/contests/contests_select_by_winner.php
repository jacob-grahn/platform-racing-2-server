<?php

function contests_select_by_winner($pdo, $winner_id)
{
    $stmt = $pdo->prepare('
        SELECT contest_id
          FROM contest_winners
         WHERE winner_id = :winner_id
    ');
    $stmt->bindValue(':winner_id', $winner_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contests by winner.');
    }
    
    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($contests)) {
        $winner_id = (int) $winner_id;
        throw new Exception("User #$winner_id has not won any contests.");
    }
    
    return $contests;
}
