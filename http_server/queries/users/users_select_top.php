<?php

function users_select_top($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT users.name as name,
               users.power as power,
               (rank_tokens.used_tokens + pr2.rank) AS active_rank,
               pr2.hat_array AS hats
        FROM users, pr2, rank_tokens
        WHERE users.user_id = pr2.user_id
        AND pr2.user_id = rank_tokens.user_id
        AND rank > 44
        HAVING active_rank > 49
        ORDER BY active_rank DESC, name ASC
        LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query to select top users');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
