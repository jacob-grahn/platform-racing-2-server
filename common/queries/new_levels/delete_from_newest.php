<?php

// deletes a level from newest
function delete_from_newest($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM pr2_new_levels
         WHERE level_id = :level_id
    ');
    $stmt->bindValue(":level_id", $level_id, PDO::PARAM_INT);
    $stmt->execute();
}
