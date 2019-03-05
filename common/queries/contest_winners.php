<?php


function contest_winner_insert($pdo, $contest_id, $winner_id, $awarder_ip, $awarded_by, $prizes_awarded, $comment)
{
    $stmt = $pdo->prepare('
        INSERT INTO contest_winners
                SET contest_id = :contest_id,
                    winner_id = :winner_id,
                    win_time = :win_time,
                    awarder_ip = :awarder_ip,
                    awarded_by = :awarded_by,
                    prizes_awarded = :prizes_awarded,
                    comment = :comment
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':winner_id', $winner_id, PDO::PARAM_INT);
    $stmt->bindValue(':win_time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':awarder_ip', $awarder_ip, PDO::PARAM_STR);
    $stmt->bindValue(':awarded_by', $awarded_by, PDO::PARAM_STR);
    $stmt->bindValue(':prizes_awarded', $prizes_awarded, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record winner.');
    }

    return true;
}


function contest_winners_select_all($pdo, $start, $count)
{
    $stmt = $pdo->prepare('
        SELECT cw.contest_id AS contest_id,
               cw.winner_id AS winner_id,
               cw.win_time AS win_time,
               cw.awarder_ip AS awarder_ip,
               cw.awarded_by AS awarded_by,
               cw.prizes_awarded AS prizes_awarded,
               cw.comment AS comment,
               c.contest_name AS contest_name
          FROM contest_winners cw
          LEFT JOIN contests c ON c.contest_id = cw.contest_id
         ORDER BY win_time DESC
         LIMIT :start, :count
    ');
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select all contest winners.');
    }

    $winners = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($winners)) {
        throw new Exception('No one has won any contests. :(');
    }

    return $winners;
}


function contest_winners_select_by_contest($pdo, $contest_id, $limit = true, $start = 0, $count = 25, $sup_err = false)
{
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
        if ($sup_err === false) {
            throw new Exception('No winners found for this contest with those search parameters.');
        } else {
            return false;
        }
    }

    return $winners;
}


function throttle_awards($pdo, $contest_id, $host_id)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
          FROM contest_winners
         WHERE contest_id = :contest_id
           AND awarded_by = :host_id
           AND win_time > UNIX_TIMESTAMP(NOW() - INTERVAL 6 DAY)
    ");
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not perform query throttle_awards.");
    }

    return (int) $stmt->fetchColumn();
}
