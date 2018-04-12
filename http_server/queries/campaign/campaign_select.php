<?php

function campaign_select($pdo)
{
    $stmt = $pdo->prepare('
        SELECT level_id, campaign, level_num, prize, prize_type, prize_id
        FROM pr2_campaign
        ORDER BY campaign ASC
    ');

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query campaign_select.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
