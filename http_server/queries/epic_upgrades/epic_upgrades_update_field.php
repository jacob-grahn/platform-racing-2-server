<?php

function epic_upgrades_update_field($pdo, $user_id, $type, $part_array)
{
    switch ($type) {
        case 'eHat':
            $field = 'epic_hats';
            break;
        case 'eHead':
            $field = 'epic_heads';
            break;
        case 'eBody':
            $field = 'epic_bodies';
            break;
        case 'eFeet':
            $field = 'epic_feet';
            break;
        default:
            throw new Exception('Unknown epic part type.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO epic_upgrades
        SET user_id = :user_id,
            $field = :part_array
        ON DUPLICATE KEY UPDATE
            $field = :part_array
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_array', $part_array, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update epic_upgrade field');
    }

    return $result;
}
