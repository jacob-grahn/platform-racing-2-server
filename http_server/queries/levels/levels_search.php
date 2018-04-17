<?php

function levels_search(
    $pdo,
    $search,
    $in_mode = 'user',
    $in_start = 0,
    $in_count = 9,
    $in_order = 'date',
    $in_dir = 'desc'
) {
    $start = min(max((int)$in_start, 0), 100);
    $count = min(max((int)$in_count, 0), 100);

    // search mode
    if ($in_mode == 'title') {
        $where = 'MATCH (title) AGAINST (:search IN BOOLEAN MODE)';
        // if title, don't show pw levels
        $live_cond = '(pr2_levels.live = 1 AND pr2_levels.pass IS NULL)';
    } else {
        $where = 'users.name = :search';
        // if user, show pw levels
        $live_cond = '(pr2_levels.live = 1 OR (pr2_levels.live = 0 AND pr2_levels.pass IS NOT NULL))';
    }

    // order by
    $order_by = 'pr2_levels.';
    if ($in_order == 'rating') {
        $order_by .= 'rating';
    } elseif ($in_order == 'alphabetical') {
        $order_by .= 'title';
    } elseif ($in_order == 'popularity') {
        $order_by .= 'play_count';
    } else {
        $order_by .= 'time';
    }

    // direction
    if ($in_dir == 'asc') {
        $dir = 'ASC';
    } else {
        $dir = 'DESC';
    }

    // get the levels
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
           AND $live_cond
         ORDER BY $order_by $dir
         LIMIT $start, $count
    ");
    $stmt->bindValue(':search', $search, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not search levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
