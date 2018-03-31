<?php

function contests_select_by_winner($pdo, $winner_name)
{
    $stmt = $pdo->prepare('
        SELECT contest_id
          FROM contest_winners
         WHERE winner_name = :winner_name
    ');
    $stmt->bindValue(':winner_name', $winner_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contests by winner.');
    }
    
    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($contests)) {
        $winner_name = htmlspecialchars($winner_name);
        throw new Exception("The user \"$winner_name\" has not won any contests.");
    }
    
    return $contests;
}
