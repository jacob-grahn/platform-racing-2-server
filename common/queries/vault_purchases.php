<?php


function vault_purchase_complete($pdo, $order_id, $start_time)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        UPDATE vault_purchases
           SET status = "complete",
               order_time = :order_time,
               start_time = :start_time
         WHERE purchase_id = :order_id
    ');
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->bindValue(':order_time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':start_time', $start_time, PDO::PARAM_INT);
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
               order_time = :time
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


function vault_purchase_select_active($pdo, $slug, $user_id, $guild_id)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT *
          FROM vault_purchases
         WHERE slug = :slug
               AND (
                 user_id = :user_id
                 OR guild_id = :guild_id
               )
               AND start_time > UNIX_TIMESTAMP() - (quantity * 3600)
         ORDER BY start_time DESC
         LIMIT 1
    ');
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not select an active purchase.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function vault_purchases_select_active($pdo)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        SELECT *
          FROM vault_purchases
         WHERE start_time > UNIX_TIMESTAMP() - (quantity * 3600)
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select active vault purchases.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
