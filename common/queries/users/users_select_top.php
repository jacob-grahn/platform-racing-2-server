<?php

function users_select_top($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT u.name AS name,
               u.power AS power,
               SUM(IFNULL(rt.used_tokens, 0) + pr2.rank) AS active_rank,
               pr2.hat_array AS hats
          FROM users u
          LEFT JOIN pr2 ON pr2.user_id = u.user_id
          LEFT JOIN rank_tokens rt ON rt.user_id = pr2.user_id
         WHERE pr2.rank > 44
         GROUP BY name, power, pr2.rank, rt.used_tokens, hats
        HAVING active_rank > 49
         ORDER BY active_rank DESC, name ASC
         LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query users_select_top.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
