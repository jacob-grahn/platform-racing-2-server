<?php

function levels_select_campaign($pdo)
{
    
    $stmt = $pdo->prepare('
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
                 users.user_id
            FROM pr2_campaign,
                 pr2_levels,
                 users
           WHERE pr2_campaign.level_id = pr2_levels.level_id
             AND pr2_levels.user_id = users.user_id
        ORDER BY pr2_campaign.campaign ASC, pr2_campaign.level_num ASC
           LIMIT 0, 81
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select campaign levels.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
