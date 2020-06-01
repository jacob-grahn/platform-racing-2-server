<?php

function levels_reported_archive($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        UPDATE levels_reported
           SET archived = 1
         WHERE level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not archive the report of level #$level_id.");
    }

    return $result;
}


function levels_reported_check_existing($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
          FROM levels_reported
         WHERE level_id = :level_id
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not check if this level has been reported already.");
    }

    return (bool) $stmt->fetchColumn();
}


function levels_reported_insert($pdo, $level_id, $version, $cid, $cip, $title, $note, $rid, $rip, $reason)
{
    $stmt = $pdo->prepare('
        INSERT INTO levels_reported
           SET level_id = :level_id,
               version = :version,
               creator_user_id = :cid,
               creator_ip = :cip,
               title = :title,
               note = :note,
               reporter_user_id = :rid,
               reporter_ip = :rip,
               report_reason = :reason,
               reported_time = :time
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':version', $version, PDO::PARAM_INT);
    $stmt->bindValue(':cid', $cid, PDO::PARAM_INT);
    $stmt->bindValue(':cip', $cip, PDO::PARAM_STR);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':rid', $rid, PDO::PARAM_INT);
    $stmt->bindValue(':rip', $rip, PDO::PARAM_STR);
    $stmt->bindValue(':reason', $reason, PDO::PARAM_STR);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not report the level.');
    }

    return $result;
}


function levels_reported_select($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT levels_reported.*, u1.name as creator, u2.name as reporter
          FROM levels_reported, users u1, users u2
         WHERE creator_user_id = u1.user_id
           AND reporter_user_id = u2.user_id
         ORDER BY reported_time desc
         LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not fetch the list of reported levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function levels_reported_select_unarchived_recent($pdo)
{
    $stmt = $pdo->prepare('
        SELECT
          lr.level_id, lr.title, u.name as author
        FROM
          levels_reported lr, users u
        WHERE
          lr.creator_user_id = u.user_id
          AND lr.archived = 0
        ORDER BY
          reported_time DESC
        LIMIT 30
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not fetch the list of recently reported levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function level_report_select_load_info($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT lr.version as reported_version, l.*
          FROM levels_reported lr
          LEFT JOIN levels l ON l.level_id = lr.level_id
         WHERE lr.level_id = :level_id
         LIMIT 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve level report.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
