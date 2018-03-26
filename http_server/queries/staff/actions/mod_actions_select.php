<?php

function mod_actions_select($pdo, $in_start, $in_count)
{
    $start = max((int) $in_start, 0);
    $count = min(max((int) $in_count, 0), 100);

    $stmt = $pdo->prepare('
          SELECT *
            FROM mod_actions
           ORDER BY time DESC
           LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not retrieve the moderator action log.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
