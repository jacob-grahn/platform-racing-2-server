<?php

function contests_select_by_winner($pdo, $pr2_name)
{
    $stmt = $pdo->prepare('
        SELECT contest_id
        FROM contest_winners
        WHERE pr2_name = :pr2_name
    ');
    $stmt->bindValue(':pr2_name', $pr2_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not select contests by winner.');
    }
    
    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($contests)) {
        throw new Exception("The user \"$pr2_name\" has not won any contests.");
    }
    
    return $contests;
}
