<?php


function campaign_level_select_by_id($pdo, $level_id)
{
    $stmt = $pdo->prepare('
        SELECT campaign, level_num
        FROM campaigns
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


function campaign_select_by_id($pdo, $campaign_id)
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM campaigns
        WHERE campaign = :campaign_id
        ORDER BY level_num
    ');
    $stmt->bindValue(':campaign_id', $campaign_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query campaign_select_by_id.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function campaign_select($pdo)
{
    $stmt = $pdo->prepare('
        SELECT level_id, campaign, level_num, prize, prize_type, prize_id
        FROM campaigns
        ORDER BY campaign ASC
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query campaign_select.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function campaign_update($pdo, $campaign_id, $levels)
{
    if (count($levels) !== 9) {
        throw new Exception('Nine levels must be specified.');
    }

    foreach ($levels as $key => $data) {
        $level_num = $key + 1;
        $level = $levels[$key];

        // sanity: all info present?
        if (empty($level->level_id) || empty($level->prize_type) || empty($level->prize_id)) {
            throw new Exception("Some data is missing for level #$level_num.");
        }

        $stmt = $pdo->prepare('
            UPDATE campaigns
            SET level_id = :level_id,
                prize_type = :prize_type,
                prize_id = :prize_id
            WHERE campaign = :campaign_id
            AND level_num = :level_num
        ');
        $stmt->bindValue(':campaign_id', $campaign_id, PDO::PARAM_INT);
        $stmt->bindValue(':level_num', $key + 1, PDO::PARAM_INT);
        $stmt->bindValue(':level_id', $level->level_id, PDO::PARAM_INT);
        $stmt->bindValue(':prize_type', $level->prize_type, PDO::PARAM_STR);
        $stmt->bindValue(':prize_id', $level->prize_id, PDO::PARAM_INT);
        $result = $stmt->execute();

        if ($result === false) {
            throw new Exception("Could not update campaign on level #$level_num.");
        }
    }

    return $result;
}
