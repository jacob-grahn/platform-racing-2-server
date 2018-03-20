<?php

function campaign_select_by_id ($pdo, $campaign_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM pr2_campaign
        WHERE campaign = p_id
        ORDER BY level_num
    ');
    $stmt->bindValue(':campaign_id', $campaign_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select campaign');
    }

    return $stmt->fetchAll();
}
