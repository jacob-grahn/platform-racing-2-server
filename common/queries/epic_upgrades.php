<?php


function admin_epic_upgrades_update($pdo, $user_id, $ehats, $eheads, $ebodies, $efeet)
{
    $stmt = $pdo->prepare('
        UPDATE epic_upgrades
           SET epic_hats = :ehats,
               epic_heads = :eheads,
               epic_bodies = :ebodies,
               epic_feet = :efeet
         WHERE user_id = :user_id
        ');
    $stmt->bindValue(':ehats', $ehats, PDO::PARAM_STR);
    $stmt->bindValue(':eheads', $eheads, PDO::PARAM_STR);
    $stmt->bindValue(':ebodies', $ebodies, PDO::PARAM_STR);
    $stmt->bindValue(':efeet', $efeet, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update epic upgrades data.");
    }

    return true;
}


function epic_upgrades_select($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('SELECT * FROM epic_upgrades WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve epic upgrades data from the database.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        if ($suppress_error === false) {
            throw new Exception("Could not find epic upgrades data for user #$user_id.");
        } else {
            return false;
        }
    }

    return $row;
}


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


function epic_upgrades_upsert($pdo, $user_id, $epic_hats, $epic_heads, $epic_bodies, $epic_feet)
{
    $stmt = $pdo->prepare('
        INSERT INTO epic_upgrades
        SET user_id = :user_id,
    		epic_hats = :epic_hats,
    		epic_heads = :epic_heads,
    		epic_bodies = :epic_bodies,
    		epic_feet = :epic_feet
        ON DUPLICATE KEY UPDATE
    		epic_hats = :epic_hats,
    		epic_heads = :epic_heads,
    		epic_bodies = :epic_bodies,
    		epic_feet = :epic_feet
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':epic_hats', $epic_hats, PDO::PARAM_STR);
    $stmt->bindValue(':epic_heads', $epic_heads, PDO::PARAM_STR);
    $stmt->bindValue(':epic_bodies', $epic_bodies, PDO::PARAM_STR);
    $stmt->bindValue(':epic_feet', $epic_feet, PDO::PARAM_STR);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not upsert into epic_upgrades');
    }

    return $result;
}
