<?php

function contest_winners_select_by_contest(
    $pdo,
    $contest_id,
    $limit = true,
    $start = 0,
    $count = 25,
    $suppress_error = false
) {
    $limit_sql = '';
    if ($limit === true) {
        $limit_sql = 'LIMIT :start, :count';
    }

    $stmt = $pdo->prepare("
        SELECT winner_id, win_time, awarded_by, prizes_awarded, awarder_ip, comment
          FROM contest_winners
         WHERE contest_id = :contest_id
         ORDER BY win_time DESC
         $limit_sql
    ");
    if ($limit === true) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    }
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select contest winners.');
    }

    $winners = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($winners)) {
        if ($suppress_error === false) {
            throw new Exception('No winners found for this contest with those search parameters.');
        } else {
            return false;
        }
    }

    return $winners;
}
