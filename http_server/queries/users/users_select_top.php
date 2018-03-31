<?php

function users_select_top($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT users.name AS name,
               users.power AS power,
               pr2.rank AS rank,
               pr2.hat_array AS hats
               rank_tokens.used_tokens AS used_tokens,
          FROM users
          LEFT JOIN pr2 ON pr2.user_id = users.user_id
          LEFT JOIN rank_tokens ON rank_tokens.user_id = pr2.user_id
         WHERE rank > 44
        HAVING SUM(pr2.rank + rank_tokens.used_tokens) > 49
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
