<?php


function contest_insert($pdo, $name, $desc, $url, $host_id, $awarding, $max_awards, $active)
{
    $stmt = $pdo->prepare('
        INSERT INTO contests
                SET contest_name = :name,
                    description = :desc,
                    url = :url,
                    user_id = :host_id,
                    awarding = :awarding,
                    max_awards = :max_awards,
                    active = :active,
                    updated = :updated
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $stmt->bindValue(':awarding', $awarding, PDO::PARAM_STR);
    $stmt->bindValue(':max_awards', $max_awards, PDO::PARAM_INT);
    $stmt->bindValue(':active', $active, PDO::PARAM_INT);
    $stmt->bindValue(':updated', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not insert new contest.');
    }

    // return the new contest's ID
    return $pdo->lastInsertId();
}


function contest_select($pdo, $contest_id, $active_only = true, $suppress_error = false)
{
    if ($active_only === true) {
        $active_cond = 'AND active = 1';
    } else {
        $active_cond = '';
    }

    $stmt = $pdo->prepare("
        SELECT contest_id, contest_name, description, url, user_id, awarding, max_awards, active
        FROM contests
        WHERE contest_id = :contest_id
        $active_cond
        LIMIT 1
    ");
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select contest.');
    }

    $contest = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($contest)) {
        if ($suppress_error === false) {
            if ($active_only === true) {
                throw new Exception("Could not find an active contest with that ID.");
            } else {
                throw new Exception("Could not find a contest with that ID.");
            }
        } else {
            return false;
        }
    }

    return $contest;
}


function contest_update($pdo, $contest_id, $name, $desc, $url, $host_id, $awarding, $max_awards, $active)
{
    $stmt = $pdo->prepare('
        UPDATE contests
           SET contest_name = :name,
               description = :desc,
               url = :url,
               user_id = :host_id,
               awarding = :awarding,
               max_awards = :max_awards,
               active = :active,
               updated = :updated
         WHERE contest_id = :contest_id
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $stmt->bindValue(':awarding', $awarding, PDO::PARAM_STR);
    $stmt->bindValue(':max_awards', $max_awards, PDO::PARAM_INT);
    $stmt->bindValue(':active', $active, PDO::PARAM_INT);
    $stmt->bindValue(':updated', time(), PDO::PARAM_INT);
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update contest #$contest_id.");
    }

    return true;
}


function contests_delete_old($pdo)
{
    $yearago = time() - 31536000;

    $stmt = $pdo->prepare("
        DELETE FROM contests
        WHERE active = 0
        AND updated < $yearago
    ");
    $result = $stmt->execute();

    if ($result === false) {
        return false;
    }
}


function contests_select_by_winner($pdo, $winner_id)
{
    $stmt = $pdo->prepare('
        SELECT contest_id
          FROM contest_winners
         WHERE winner_id = :winner_id
    ');
    $stmt->bindValue(':winner_id', $winner_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select contests by winner.');
    }

    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($contests)) {
        $winner_id = (int) $winner_id;
        throw new Exception("User #$winner_id has not won any contests.");
    }

    return $contests;
}


function contests_select($pdo, $active_only = true)
{
    $sql = '';
    $err = 'all';
    if ($active_only === true) {
        $sql = 'WHERE active = 1';
        $err = 'active';
    }

    $stmt = $pdo->prepare("
        SELECT contest_id, contest_name, description, url, user_id, awarding, active, max_awards
          FROM contests
          $sql
         ORDER BY active DESC, contest_name ASC
    ");
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not select $err contests.");
    }

    $contests = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($contests)) {
        return false;
    }

    return $contests;
}
