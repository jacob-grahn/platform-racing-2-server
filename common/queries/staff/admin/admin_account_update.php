<?php

function admin_user_update($pdo, $user_id, $name, $email, $guild)
{
    $stmt = $pdo->prepare('
        UPDATE users
           SET name = :name,
               email = :email,
               guild = :guild
         WHERE user_id = :user_id
        ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':guild', $guild, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update user data.");
    }

    return true;
}

function admin_pr2_update($pdo, $user_id, $hats, $heads, $bodies, $feet)
{
    $stmt = $pdo->prepare('
        UPDATE pr2
           SET hat_array = :hats,
               head_array = :heads,
               body_array = :bodies,
               feet_array = :feet
         WHERE user_id = :user_id
        ');
    $stmt->bindValue(':hats', $hats, PDO::PARAM_STR);
    $stmt->bindValue(':heads', $heads, PDO::PARAM_STR);
    $stmt->bindValue(':bodies', $bodies, PDO::PARAM_STR);
    $stmt->bindValue(':feet', $feet, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update PR2 data.");
    }

    return true;
}

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
