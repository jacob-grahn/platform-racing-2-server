<?php


function vault_items_select($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM `vault_items` WHERE active = 1 ORDER BY placement ASC;');
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Could not get a list of items in the vault.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function vault_item_select($pdo, $slug)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM vault_items
         WHERE active = 1
           AND slug = :slug
         ORDER BY placement ASC
         LIMIT 1
    ');
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('No vault item with that slug.');
    }

    return $stmt->fetchAll(PDO::PARAM_STR);
}
