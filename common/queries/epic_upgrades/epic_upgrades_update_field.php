<?php

function epic_upgrades_update_field($pdo, $user_id, $type, $part_array)
{
    $type = strtolower($type);
    switch ($type) {
        case 'ehat':
            $field = 'epic_hats';
            break;
        case 'ehead':
            $field = 'epic_heads';
            break;
        case 'ebody':
            $field = 'epic_bodies';
            break;
        case 'efeet':
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
        $user_id = (int) $user_id;
        throw new Exception("Could not update user #$user_id's epic_upgrades data on column $field.");
    }

    return $result;
}
