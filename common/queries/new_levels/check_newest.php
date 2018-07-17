<?php

// check newest pg 1 for 3 or more recent levels from this account or ip
function check_newest($pdo, $name, $ip)
{
    // make our end array
    $matches = array();

    // account check
    $newest = file_get_contents(WWW_ROOT . '/files/lists/newest/1');
    parse_str($newest, $levels_array);
    $levels_data = array_chunk($levels_array, 12);

    foreach ($levels_data as $level) {
        $level_id = (int) $level[0];
        $level_creator = $level[7];

        if (strtolower($name) === strtolower($level_creator)) {
            array_push($matches, $level_id);
        }
    }


    // ip check
    $stmt = $pdo->prepare('
        SELECT level_id as id, ip
          FROM pr2_new_levels
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
