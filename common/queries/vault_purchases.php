<?php


function vault_purchase_complete($pdo, $order_id)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        UPDATE vault_purchases
           SET status = "complete"
         WHERE purchase_id = :order_id
    ');
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not complete purchase.');
    }

    return $result;
}


function vault_purchase_insert($pdo, $user_id, $guild_id, $slug, $coins, $quantity)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        INSERT INTO vault_purchases
           SET user_id = :user_id,
               guild_id = :guild_id,
               slug = :slug,
               coins = :coins,
               quantity = :quantity,
               time = :time
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':coins', $coins, PDO::PARAM_INT);
    $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not record vault item purchase in the database.');
    }

    return $pdo->lastInsertId();
}


function vault_purchases_select_recent($pdo)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT *
          FROM vault_purchases
         WHERE time > UNIX_TIMESTAMP() - (quantity * 3600)
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select recent vault purchases.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
