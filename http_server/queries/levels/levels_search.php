<?php

function levels_search ($pdo, $search, $in_mode = 'title', $in_start = 0, $in_count = 9, $in_order_by = 'date', $in_dir = 'desc')
{
    $start = min( max( (int)$in_start, 0), 100 );
    $count = min( max( (int)$in_count, 0), 100 );

    switch($in_order_by)
    {
        case 'rating':
            $order_by = 'pr2_levels.rating';
            break;
        case 'date':
            $order_by = 'pr2_levels.time';
            break;
        case 'alphabetical':
            $order_by = 'pr2_levels.title';
            break;
        case 'popularity':
            $order_by = 'pr2_levels.play_count';
            break;
        default:
            $order_by = 'pr2_levels.time';
            break;
    }

    switch ($in_dir)
    {
        case 'asc':
            $dir = 'ASC';
            break;
        case 'desc':
            $dir = 'DESC';
            break;
        default:
            $dir = 'DESC';
            break;
    }

    switch ($in_mode)
    {
        case 'title':
            $where = 'users.name = :search';
            break;
        case 'user':
            $where = 'users.name = :search';
            break;
        default:
            $where = 'MATCH (title) AGAINST (:search IN BOOLEAN MODE)';
            break;
    }

    $stmt = $pdo->prepare("
        SELECT pr2_levels.level_id,
               pr2_levels.version,
               pr2_levels.title,
               pr2_levels.rating,
               pr2_levels.play_count,
               pr2_levels.min_level,
               pr2_levels.note,
               pr2_levels.live,
               pr2_levels.type,
               users.name,
               users.power,
               pr2_levels.pass
        FROM pr2_levels, users
        WHERE $where
        AND pr2_levels.user_id = users.user_id
        AND (pr2_levels.live = 1 OR pr2_levels.pass IS NOT NULL)
        ORDER BY $order_by $dir
        LIMIT $start, $count
    ");
    $stmt->bindValue(':search', $search, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not search levels');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
