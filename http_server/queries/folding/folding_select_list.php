<?php

// TO-DO: is this needed?
function folding_select_list($pdo)
{
    $stmt = $pdo->prepare('
        SELECT folding_at_home.*, users.name, users.status
          FROM folding_at_home, users
         WHERE folding_at_home.user_id = users.user_id
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query folding_select_list.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
