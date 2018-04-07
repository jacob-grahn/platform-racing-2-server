<?php

function pr2_update_part_array($pdo, $user_id, $type, $part_array)
{
    switch ($type) {
        case 'hat':
            $field = 'hat_array';
            break;
        case 'head':
            $field = 'head_array';
            break;
        case 'body':
            $field = 'body_array';
            break;
        case 'feet':
            $field = 'feet_array';
            break;
        default:
            throw new Exception('Unknown part type.');
    }

    $stmt = $pdo->prepare("
        UPDATE pr2
           SET $field = :part_array
         WHERE user_id = :user_id
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_array', $part_array, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        $user_id = (int) $user_id;
        throw new Exception("Could not update user #$user_id's PR2 player data on column $field.");
    }

    return $result;
}
