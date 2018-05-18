<?php

function contest_insert($pdo, $name, $desc, $url, $host_id, $awarding, $max_awards, $active)
{
    $stmt = $pdo->prepare('
        INSERT INTO contests
                SET contest_name = :name,
                    description = :desc,
                    url = :url,
                    user_id = :host_id,
                    awarding = :awarding,
                    max_awards = :max_awards,
                    active = :active,
                    updated = :updated
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':host_id', $host_id, PDO::PARAM_INT);
    $stmt->bindValue(':awarding', $awarding, PDO::PARAM_STR);
    $stmt->bindValue(':max_awards', $max_awards, PDO::PARAM_INT);
    $stmt->bindValue(':active', $active, PDO::PARAM_INT);
    $stmt->bindValue(':updated', time(), PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not insert new contest.');
    }
    
    // return the new contest's ID
    return $pdo->lastInsertId();
}
