<?php

function campaign_update($pdo, $campaign_id, $level_num, $level_id, $prize_type, $prize_id)
{
    $stmt = $pdo->prepare('
        UPDATE pr2_campaign
        SET level_id = :level_id,
            prize_type = :prize_type,
            prize_id = :prize_id
        WHERE campaign = :campaign_id
        AND level_num = :level_num
    ');
    $stmt->bindValue(':campaign_id', $campaign_id, PDO::PARAM_INT);
    $stmt->bindValue(':level_num', $level_num, PDO::PARAM_INT);
    $stmt->bindValue(':level_id', $level_id, PDO::PARAM_INT);
    $stmt->bindValue(':prize_type', $prize_type, PDO::PARAM_STR);
    $stmt->bindValue(':prize_id', $prize_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update campaign on level $level_num.");
    }

    return $result;
}
