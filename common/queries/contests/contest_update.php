<?php

function contest_update($pdo, $contest_id, $name, $desc, $url, $host_id, $awarding, $max_awards, $active)
{
    $stmt = $pdo->prepare('
        UPDATE contests
           SET contest_name = :name,
               description = :desc,
               url = :url,
               user_id = :host_id,
               awarding = :awarding,
               max_awards = :max_awards,
               active = :active,
               updated = :updated
         WHERE contest_id = :contest_id
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $stmt->bindValue(':awarding', $awarding, PDO::PARAM_STR);
    $stmt->bindValue(':max_awards', $max_awards, PDO::PARAM_INT);
    $stmt->bindValue(':active', $active, PDO::PARAM_INT);
    $stmt->bindValue(':updated', time(), PDO::PARAM_INT);
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update contest #$contest_id.");
    }
    
    return true;
}
