<?php


function artifact_location_select($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM artifact_location WHERE artifact_id = 1 LIMIT 1');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve artifact location.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function artifact_location_update_bubbles_winner($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
        SET bubbles_winner = :user_id
        WHERE artifact_id = 1
        AND bubbles_winner = 0
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update artifact first finder.');
    }

    return $result;
}


function artifact_location_update_first_finder($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
        SET first_finder = :user_id
        WHERE artifact_id = 1
        AND first_finder = 0
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not update artifact first finder.');
    }

    return $result;
}


function artifact_location_instant_update($pdo, $level_id, $x, $y, $rot)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
           SET level_id = :level_id,
               x = :x,
               y = :y,
               rot = :rot,
               set_time = UNIX_TIMESTAMP(NOW()),
               updated_time = UNIX_TIMESTAMP(NOW()),
               first_finder = 0,
               bubbles_winner = 0
         WHERE artifact_id = 1
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':x', $x, PDO::PARAM_INT);
    $stmt->bindValue(':y', $y, PDO::PARAM_INT);
    $stmt->bindValue(':rot', $rot, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query artifact_location_instant_update.');
    }

    return $result;
}


function artifact_location_schedule_update($pdo, $level_id, $x, $y, $rot, $set_time)
{
    $stmt = $pdo->prepare('
        INSERT INTO artifact_location
        SET
          artifact_id = 2,
          level_id = :level_id,
          x = :x,
          y = :y,
          rot = :rot,
          set_time = :set_time,
          updated_time = UNIX_TIMESTAMP(NOW())
        ON DUPLICATE KEY UPDATE
          level_id = :upd_level_id,
          x = :upd_x,
          y = :upd_y,
          rot = :upd_rot,
          set_time = :upd_set_time,
          updated_time = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':x', $x, PDO::PARAM_INT);
    $stmt->bindValue(':y', $y, PDO::PARAM_INT);
    $stmt->bindValue(':rot', $rot, PDO::PARAM_INT);
    $stmt->bindValue(':set_time', $set_time, PDO::PARAM_INT);
    $stmt->bindValue(':upd_level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':upd_x', $x, PDO::PARAM_INT);
    $stmt->bindValue(':upd_y', $y, PDO::PARAM_INT);
    $stmt->bindValue(':upd_rot', $rot, PDO::PARAM_INT);
    $stmt->bindValue(':upd_set_time', $set_time, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not schedule artifact location update.');
    }

    return $result;
}


function artifact_location_update_by_id($pdo, $artifact_id, $level_id, $x, $y, $rot, $set_time)
{
    $stmt = $pdo->prepare('
        UPDATE artifact_location
           SET level_id = :level_id,
               x = :x,
               y = :y,
               rot = :rot,
               set_time = :set_time,
               updated_time = UNIX_TIMESTAMP(NOW())
         WHERE artifact_id = :artifact_id
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':x', $x, PDO::PARAM_INT);
    $stmt->bindValue(':y', $y, PDO::PARAM_INT);
    $stmt->bindValue(':rot', $rot, PDO::PARAM_INT);
    $stmt->bindValue(':artifact_id', $artifact_id, PDO::PARAM_INT);
    $stmt->bindValue(':set_time', $set_time, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query artifact_location_instant_update.');
    }

    return $result;
}


function artifact_location_delete_old($pdo)
{
    $stmt1 = $pdo->query('DELETE FROM artifact_location WHERE artifact_id = 1');
    $stmt2 = $pdo->query('UPDATE artifact_location SET artifact_id = artifact_id - 1');
    if (!$stmt1 || !$stmt2) {
        throw new Exception('Could not perform query artifact_location_delete_old.');
    }
    return true;
}


function artifact_locations_select($pdo)
{
    $stmt = $pdo->query("
        SELECT *
        FROM artifact_location
        ORDER BY artifact_id ASC
    ");

    if ($stmt === false) {
        throw new Exception('Could not perform query artifact_locations_select.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
