<?php

function purchase_insert($pdo, $user_id, $guild_id, $product, $kong_id, $order_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO purchases
           SET user_id = :user_id,
               guild_id = :guild_id,
               product = :product,
               kong_id = :kong_id,
               order_id = :order_id,
               date = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':product', $product, PDO::PARAM_STR);
    $stmt->bindValue(':kong_id', $kong_id, PDO::PARAM_STR);
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not record purchase in the database.');
    }

    return $result;
}
