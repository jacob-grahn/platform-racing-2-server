<?php


function vault_coins_comp_order_insert($pdo, $pr2_user_id, $coins_before, $coins, $comment)
{
    $time = time();
    $stmt = $pdo->prepare('
        INSERT INTO
          vault_coins_orders
        SET
          order_id = :order_id,
          capture_id = "manual",
          pr2_user_id = :pr2_user_id,
          coins_before = :coins_before,
          coins = :coins,
          net_money = "0.00",
          created_time = :crtime,
          completed_time = :cotime,
          status = "complete",
          comment = :comment
    ');
    $stmt->bindValue(':order_id', "comp-$time", PDO::PARAM_STR);
    $stmt->bindValue(':pr2_user_id', $pr2_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':coins_before', $coins_before, PDO::PARAM_INT);
    $stmt->bindValue(':coins', $coins, PDO::PARAM_INT);
    $stmt->bindValue(':crtime', $time, PDO::PARAM_INT);
    $stmt->bindValue(':cotime', $time, PDO::PARAM_INT);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not insert comp order.');
    }

    // return the inserted order ID
    return $pdo->lastInsertId();
}


function vault_coins_order_complete($pdo, $order_id, $net_money, $capture_id)
{
    $stmt = $pdo->prepare('
        UPDATE
          vault_coins_orders
        SET
          status = "complete",
          completed_time = :time,
          net_money = :money,
          capture_id = :capture_id
        WHERE
          order_id = :order_id
    ');
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':money', $net_money, PDO::PARAM_STR);
    $stmt->bindValue(':capture_id', $capture_id, PDO::PARAM_STR);
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not complete your order.');
    }
}


function vault_coins_order_insert($pdo, $user, $coins_package, $order_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO
          vault_coins_orders
        SET
          order_id = :order_id,
          pr2_user_id = :user_id,
          coins_before = :coins_pre,
          coins = :coins,
          bonus = :bonus,
          price = :price,
          created_time = :time
    ');
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->bindValue(':coins_pre', $user->coins, PDO::PARAM_INT);
    $stmt->bindValue(':coins', $coins_package->coins, PDO::PARAM_INT);
    $stmt->bindValue(':bonus', $coins_package->bonus, PDO::PARAM_INT);
    $stmt->bindValue(':price', $coins_package->price, PDO::PARAM_INT);
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Your order could not be recorded.');
    }

    return $result;
}


function vault_coins_order_refund($pdo, $order_id, $comment)
{
    $stmt = $pdo->prepare('
        UPDATE
          vault_coins_orders
        SET
          status = "refunded",
          net_money = "0.00",
          refunded_time = :time,
          comment = :comment
        WHERE
          order_id = :order_id
    ');
    $stmt->bindValue(':time', time(), PDO::PARAM_INT);
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not process refund.');
    }

    return $result;
}


function vault_coins_order_select($pdo, $order_id)
{
    $stmt = $pdo->prepare('SELECT * FROM vault_coins_orders WHERE order_id = :order_id LIMIT 1');
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not retrieve coins order.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
