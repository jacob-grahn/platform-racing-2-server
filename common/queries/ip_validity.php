<?php


function ip_validity_delete($pdo, $ip)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        DELETE FROM ip_validity
        WHERE ip = :ip
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Could not delete IP validity record from the database.');
    }

    return $result;
}


function ip_validity_delete_invalid($pdo)
{
    $stmt = $pdo->query('DELETE FROM ip_validity WHERE valid = 0');
    return $stmt->rowCount();
}


function ip_validity_upsert($pdo, $ip, $valid)
{
    db_set_encoding($pdo, 'utf8mb4');
    $stmt = $pdo->prepare('
        INSERT INTO
          ip_validity
        SET
          ip = :ip,
          valid = :valid_ins,
          time = UNIX_TIMESTAMP(NOW())
        ON DUPLICATE KEY UPDATE
          valid = :valid_upd,
          time = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':valid_ins', $valid, PDO::PARAM_INT);
    $stmt->bindValue(':valid_upd', $valid, PDO::PARAM_INT);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Could not update IP validity status.');
    }

    return $result;
}


function ip_validity_select($pdo, $ip, $show_exp = false)
{
    db_set_encoding($pdo, 'utf8mb4');
    $exp_sql = $show_exp ? '' : 'AND time > UNIX_TIMESTAMP(NOW() - INTERVAL 2 MONTH)';
    $stmt = $pdo->prepare("
        SELECT *
        FROM ip_validity
        WHERE ip = :ip
        $exp_sql
    ");
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Could not retrieve IP validity status from the database.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function ip_validity_select_list($pdo)
{
    db_set_encoding($pdo, 'utf8mb4');
    $list = $pdo->query('SELECT * FROM ip_validity ORDER BY time DESC')->fetchAll(PDO::FETCH_OBJ);

    if (empty($list)) {
        throw new Exception('No IP validity data found.');
    }

    return $list;
}
