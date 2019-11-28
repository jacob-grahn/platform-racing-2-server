<?php


function level_check_if_creator($pdo, $user_id, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT level_id
          FROM levels
         WHERE user_id = :user_id
           AND level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query level_check_if_creator.');
    }

    $level = (bool) $stmt->fetch(PDO::FETCH_OBJ);
    return $level;
}


function level_delete($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM levels
        WHERE level_id = :level_id
        LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete level.');
    }

    return $result;
}


function level_increment_play_count($pdo, $level_id, $play_count)
{
    $stmt = $pdo->prepare('
        UPDATE levels
           SET play_count = play_count + :play_count
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':play_count', $play_count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update level #$level_id's play count.");
    }

    return $result;
}


function level_insert($pdo, $title, $note, $live, $time, $ip, $min_level, $song, $user_id, $pass, $type)
{
    $stmt = $pdo->prepare('
        INSERT INTO levels
           SET title = :title,
               note = :note,
               live = :live,
               time = :time,
               ip = :ip,
               min_level = :min_level,
               song = :song,
               user_id = :user_id,
               pass = :pass,
               type = :type
    ');
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $min_level, PDO::PARAM_INT);
    $stmt->bindValue(':song', (int) $song, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not save the new level.');
    }

    return $result;
}


function level_select_by_title($pdo, $user_id, $title)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM levels
         WHERE user_id = :user_id
           AND title = :title
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not fetch level by title.');
    }

    $level = $stmt->fetch(PDO::FETCH_OBJ);
    return $level;
}


function level_select($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM levels
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query level_select.');
    }

    $level = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($level)) {
        throw new Exception('Could not find a level with that ID.');
    }

    return $level;
}


function level_unpublish($pdo, $level_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        UPDATE levels
           SET live = 0,
               pass = NULL
         WHERE level_id = :level_id
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not unpublish level.');
        } else {
            return false;
        }
    }
    
    return $result;
}


function level_update_rating($pdo, $level_id, $rating, $votes)
{
    $stmt = $pdo->prepare('
        UPDATE levels
           SET rating = :rating,
               votes = :votes
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
    $stmt->bindValue(':votes', $votes, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update level rating.');
    }

    return $result;
}


function level_update($pdo, $level_id, $title, $note, $live, $time, $ip, $min_level, $song, $version, $pass, $type)
{
    $stmt = $pdo->prepare('
        UPDATE levels
           SET title = :title,
               note = :note,
               live = :live,
               time = :time,
               ip = :ip,
               min_level = :min_level,
               song = :song,
               version = :version,
               pass = :pass,
               type = :type
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_STR);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $min_level, PDO::PARAM_INT);
    $stmt->bindValue(':song', (int) $song, PDO::PARAM_INT);
    $stmt->bindValue(':version', $version, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update level.');
    }

    return $result;
}


function levels_restore_backup($pdo, $uid, $name, $note, $live, $ip, $rank, $song, $lid, $plays, $votes, $rating, $ver)
{
    $stmt = $pdo->prepare('
        INSERT INTO levels
        SET level_id = :level_id,
            version = :version,
            title = :title,
            note = :note,
            live = :live,
            time = :time,
            ip = :ip,
            min_level = :min_level,
            song = :song,
            user_id = :user_id,
            play_count = :play_count,
            votes = :votes,
            rating = :rating
        ON DUPLICATE KEY UPDATE
            version = version + 1,
            note = :note,
            live = :live,
            min_level = :min_level,
            song = :song
    ');
    $stmt->bindValue(':user_id', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':title', $name, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $rank, PDO::PARAM_INT);
    $stmt->bindValue(':song', $song, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $lid, PDO::PARAM_INT);
    $stmt->bindValue(':play_count', $plays, PDO::PARAM_INT);
    $stmt->bindValue(':votes', $votes, PDO::PARAM_INT);
    $stmt->bindValue(':rating', $rating, PDO::PARAM_STR);
    $stmt->bindValue(':version', $ver, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not restore level backup.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function levels_search($pdo, $search, $mode = 'user', $start = 0, $count = 9, $order = 'date', $dir = 'desc')
{
    $start = min(max((int) $start, 0), 100);
    $count = min(max((int) $count, 0), 100);

    // search mode
    if ($mode === 'title') {
        $where = 'MATCH (title) AGAINST (:search IN BOOLEAN MODE)';
        // if title, don't show pw levels
        $live_cond = '(l.live = 1 AND l.pass IS NULL)';
    } else {
        $where = 'u.name = :search';
        // if user, show pw levels
        $live_cond = '(l.live = 1 OR (l.live = 0 AND l.pass IS NOT NULL))';
    }

    // order by
    $order_by = 'l.';
    if ($order === 'rating') {
        $order_by .= 'rating';
    } elseif ($order == 'alphabetical') {
        $order_by .= 'title';
    } elseif ($order == 'popularity') {
        $order_by .= 'play_count';
    } else {
        $order_by .= 'time';
    }

    // direction
    if ($dir === 'asc') {
        $dir = 'ASC';
    } else {
        $dir = 'DESC';
    }

    // get the levels
    $stmt = $pdo->prepare("
        SELECT l.level_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.pass,
               l.type,
               l.time,
               u.name,
               u.power
          FROM levels l, users u
         WHERE $where
           AND l.user_id = u.user_id
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


function levels_select_best_today($pdo)
{
    $stmt = $pdo->prepare('
        SELECT l.level_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.type,
               l.time,
               u.name,
               u.power,
               u.user_id
          FROM new_levels nl,
               levels l,
               users u
         WHERE l.user_id = u.user_id
           AND nl.level_id = l.level_id
           AND live = 1
           AND votes > 25
      ORDER BY rating DESC
         LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select today\'s best levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function levels_select_best($pdo)
{
    $stmt = $pdo->prepare('
          SELECT l.level_id,
                 l.version,
                 l.title,
                 l.rating,
                 l.play_count,
                 l.min_level,
                 l.note,
                 l.live,
                 l.type,
                 l.time,
                 u.name,
                 u.power,
                 u.user_id
            FROM best_levels bl,
                 levels l,
                 users u
           WHERE l.user_id = u.user_id
             AND bl.level_id = l.level_id
        ORDER BY rating DESC
           LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select all-time best levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function levels_select_by_owner($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT l.level_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.type,
               l.time,
               u.name,
               u.power,
               u.user_id
          FROM levels l, users u
         WHERE l.user_id = u.user_id
           AND l.user_id = :user_id
         ORDER BY l.time DESC
         LIMIT 0, 1000
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query levels_select_by_owner.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function level_select_by_rand($pdo)
{
    $stmt = $pdo->prepare('
        SELECT level_id, title, note
          FROM levels
          JOIN (SELECT CEIL(RAND() * (SELECT MAX(level_id) FROM levels)) AS rand_id) AS temp
         WHERE levels.level_id >= temp.rand_id
           AND live = 1
         ORDER BY level_id ASC
         LIMIT 1
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select a random level.');
    }

    $level = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($level)) {
        throw new Exception('Could not find a random level.');
    }

    return $level;
}


function levels_select_campaign($pdo)
{

    $stmt = $pdo->prepare('
          SELECT l.level_id,
                 l.version,
                 l.title,
                 l.rating,
                 l.play_count,
                 l.min_level,
                 l.note,
                 l.live,
                 l.type,
                 l.time,
                 u.name,
                 u.power,
                 u.user_id
            FROM campaigns c,
                 levels l,
                 users u
           WHERE c.level_id = l.level_id
             AND l.user_id = u.user_id
        ORDER BY c.campaign ASC, c.level_num ASC
           LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select campaign levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function levels_select_newest($pdo)
{
    $stmt = $pdo->prepare('
          SELECT l.level_id,
                 l.version,
                 l.title,
                 l.rating,
                 l.play_count,
                 l.min_level,
                 l.note,
                 l.live,
                 l.type,
                 l.time,
                 u.name,
                 u.power,
                 u.user_id
            FROM new_levels nl,
                 levels l,
                 users u
           WHERE l.user_id = u.user_id
             AND nl.level_id = l.level_id
             AND live = 1
        ORDER BY nl.time DESC
           LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select newest levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function levels_select_top($pdo, , $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT l.level_id,
               l.title,
               l.play_count,
               u.user_id,
               u.name,
               u.power
          FROM levels l, users u
         WHERE l.play_count > 20000
         AND l.user_id = u.user_id
         ORDER BY l.play_count DESC
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
