<?php

function campaign_level_select_by_id($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT campaign, level_num
        FROM pr2_campaign
        WHERE level_id = :level_id
        LIMIT 1
    ');
    $stmt->bindValue(":level_id", $level_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query campaign_level_select_by_id.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
