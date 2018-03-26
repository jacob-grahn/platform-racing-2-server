<?php

function contest_insert($pdo, $name, $desc, $url, $host_id, $active, $contest_id)
{
    $stmt = $pdo->prepare('
        UPDATE contests
           SET contest_name = :name,
               description = :desc,
               url = :url,
               user_id = :host_id,
               active = :active
         WHERE contest_id = :contest_id
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $stmt->bindValue(':active', $active, PDO::PARAM_INT);
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not update contest #$contest_id.");
    }
    
    return true;
}
