<?php


function admin_level_update($pdo, $level_id, $user_id, $title, $note, $live, $restricted)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        UPDATE
          levels
        SET
          user_id = :user_id,
          title = :title,
          note = :note,
          live = :live,
          restricted = :restricted
        WHERE
          level_id = :level_id
        LIMIT
          1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':restricted', $restricted, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update level.');
    }

    return $result;
}


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


function level_insert($pdo, $title, $note, $live, $time, $ip, $min_level, $song, $user_id, $pass, $type, $hats)
{
    db_set_encoding($pdo, 'utf8mb4');
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
               type = :type,
               bad_hats = :bad_hats
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
    $stmt->bindValue(':bad_hats', $hats, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not save the new level.');
    }

    return $result;
}


function level_restrict($pdo, $level_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        UPDATE levels
           SET restricted = 1
         WHERE level_id = :level_id
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not restrict level.');
        } else {
            return false;
        }
    }
    
    return $result;
}


function level_select_by_title($pdo, $user_id, $title)
{
    db_set_encoding($pdo, 'utf8mb4');
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


function level_select($pdo, $level_id, $suppress_error = false)
{
    db_set_encoding($pdo, 'utf8mb4');
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
        if (!$suppress_error) {
            throw new Exception('Could not find a level with that ID.');
        }
        return false;
    }

    return $level;
}


function level_select_from_search($pdo, $level_id)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT l.level_id,
               l.user_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.pass,
               l.type,
               l.bad_hats,
               l.time,
               u.name,
               u.power,
               u.trial_mod,
               u.ca
          FROM levels l, users u
         WHERE level_id = :level_id
           AND l.user_id = u.user_id
           AND (l.live = 1 OR (l.live = 0 AND l.pass IS NOT NULL))
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not search levels.');
    }

    $levels = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (empty($levels)) {
        throw new Exception('Could not find a level with that ID.');
    }

    return $levels;
}


function level_select_title($pdo, $level_id)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT title
          FROM levels
         WHERE level_id = :level_id
           AND (live = 1 OR (live = 0 AND pass IS NOT NULL))
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query level_select_title.');
    }

    $level = $stmt->fetch(PDO::FETCH_OBJ);
    if (empty($level)) {
        throw new Exception('Could not find a level with that ID.');
    }

    return $level->title;
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


function level_update($pdo, $lid, $title, $note, $live, $time, $ip, $rank, $song, $version, $pass, $type, $hats)
{
    db_set_encoding($pdo, 'utf8mb4');
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
               type = :type,
               bad_hats = :bad_hats,
               restricted = 0
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $lid, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':live', $live, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':min_level', $rank, PDO::PARAM_INT);
    $stmt->bindValue(':song', (int) $song, PDO::PARAM_INT);
    $stmt->bindValue(':version', $version, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':bad_hats', $hats, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update level.');
    }

    return $result;
}


function levels_restore_backup(
    $pdo,
    $uid,
    $name,
    $note,
    $live,
    $ip,
    $rank,
    $song,
    $lid,
    $plays,
    $pass,
    $type,
    $hats,
    $votes,
    $rating,
    $ver
) {
    db_set_encoding($pdo, 'utf8mb4');
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
            pass = :pass,
            type = :type,
            bad_hats = :bad_hats,
            votes = :votes,
            rating = :rating
        ON DUPLICATE KEY UPDATE
            version = version + 1,
            note = :note,
            live = :live,
            min_level = :min_level,
            song = :song,
            pass = :pass,
            type = :type,
            bad_hats = :bad_hats,
            restricted = 0
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
    $stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':bad_hats', $hats, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not restore level backup.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function levels_search($pdo, $search, $mode = 'user', $start = 0, $count = 9, $order = 'date', $dir = 'desc')
{
    db_set_encoding($pdo, 'utf8mb4');

    if ($mode === 'id') {
        if ($start === 0) { // page 1 only
            return level_select_from_search($pdo, $search);
        }
        return [];
    }

    $start = min(max((int) $start, 0), 100);
    $count = min(max((int) $count, 0), 100);

    // search mode
    if ($mode === 'title') {
        $where = 'MATCH (title) AGAINST (:search) > 0';
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
    $dir = $dir === 'asc' ? 'ASC' : 'DESC';

    // get the levels
    $stmt = $pdo->prepare("
        SELECT l.level_id,
               l.user_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.pass,
               l.type,
               l.bad_hats,
               l.time,
               u.name,
               u.power,
               u.trial_mod,
               u.ca
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


function levels_select_best_week($pdo)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT l.level_id,
               l.user_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.type,
               l.bad_hats,
               l.time,
               u.name,
               u.power,
               u.trial_mod,
               u.ca
          FROM new_levels nl,
               levels l,
               users u
         WHERE l.user_id = u.user_id
           AND nl.level_id = l.level_id
           AND live = 1
           AND votes > 25
           AND restricted = 0
      ORDER BY rating DESC
         LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select this week\'s best levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function levels_select_best($pdo)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
          SELECT l.level_id,
                 l.user_id,
                 l.version,
                 l.title,
                 l.rating,
                 l.play_count,
                 l.min_level,
                 l.note,
                 l.live,
                 l.type,
                 l.bad_hats,
                 l.time,
                 u.name,
                 u.power,
                 u.trial_mod,
                 u.ca
            FROM best_levels bl,
                 levels l,
                 users u
           WHERE l.user_id = u.user_id
             AND bl.level_id = l.level_id
             AND restricted = 0
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
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT l.level_id,
               l.user_id,
               l.version,
               l.title,
               l.rating,
               l.play_count,
               l.min_level,
               l.note,
               l.live,
               l.type,
               l.bad_hats,
               l.time,
               u.name,
               u.power,
               u.trial_mod,
               u.ca
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
    db_set_encoding($pdo, 'utf8mb4');
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
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
          SELECT l.level_id,
                 l.user_id,
                 l.version,
                 l.title,
                 l.rating,
                 l.play_count,
                 l.min_level,
                 l.note,
                 l.live,
                 l.type,
                 l.bad_hats,
                 l.time,
                 u.name,
                 u.power,
                 u.trial_mod,
                 u.ca
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
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
          SELECT l.level_id,
                 l.user_id,
                 l.version,
                 l.title,
                 l.rating,
                 l.play_count,
                 l.min_level,
                 l.note,
                 l.live,
                 l.type,
                 l.bad_hats,
                 l.time,
                 u.name,
                 u.power,
                 u.trial_mod,
                 u.ca
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
