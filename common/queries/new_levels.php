<?php


// check newest for more than 3 recent levels from this account or ip
function check_newest($pdo, $name, $ip)
{
    // make our end array
    $matches = array();

    // account check
    $newest = file_get_contents(WWW_ROOT . '/files/lists/newest/1');
    $levels = json_decode($newest);

    foreach ($levels->levels as $level) {
        $level_id = (int) $level->level_id;
        $level_creator = $level->user_name;

        if (strtolower($name) === strtolower($level_creator)) {
            array_push($matches, $level_id);
        }
    }


    // ip check
    $stmt = $pdo->prepare('
        SELECT level_id as id, ip
          FROM new_levels
      ORDER BY time DESC
         LIMIT 0, 9
    ');
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($results as $level) {
        $level_id = (int) $level->id;
        $pub_ip = $level->ip;

        if ($pub_ip == $ip) {
            if (!in_array($level_id, $matches)) {
                array_push($matches, $level_id);
            }
        }
    }

    if (count($matches) >= 3) {
        return false;
    } else {
        return true;
    }
}


// deletes a level from newest
function delete_from_newest($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM new_levels
         WHERE level_id = :level_id
    ');
    $stmt->bindValue(":level_id", $level_id, PDO::PARAM_INT);
    $stmt->execute();
}


function new_level_insert($pdo, $level_id, $time, $ip)
{
    $stmt = $pdo->prepare('
        REPLACE INTO new_levels
            SET level_id = :level_id,
                time = :time,
                ip = :ip
    ');
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not submit level to the newest levels list.');
    }

    return $result;
}


function new_levels_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM new_levels
         WHERE time < UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)
    ');

    if ($result === false) {
        throw new Exception('Could not delete levels older than a day from newest.');
    }

    return $result;
}
