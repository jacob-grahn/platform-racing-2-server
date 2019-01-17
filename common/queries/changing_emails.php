<?php


function changing_email_complete($pdo, $change_id, $ip)
{
    $stmt = $pdo->prepare('
        UPDATE changing_emails
           SET status = "complete",
               confirm_ip = :ip
         WHERE change_id = :change_id
    ');
    $stmt->bindValue(':change_id', $change_id, PDO::PARAM_INT);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not complete email change.');
    }

    return $result;
}


function changing_email_insert($pdo, $user_id, $old_email, $new_email, $code, $ip)
{
    $stmt = $pdo->prepare('
        INSERT INTO changing_emails
                SET user_id = :user_id,
                    old_email = :old_email,
                    new_email = :new_email,
                    code = :code,
                    request_ip = :ip,
                    status = "pending",
                    date = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':old_email', $old_email, PDO::PARAM_STR);
    $stmt->bindValue(':new_email', $new_email, PDO::PARAM_STR);
    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not start email change.');
    }

    return $result;
}


function changing_email_select($pdo, $code)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM changing_emails
         WHERE code = :code
           AND status = "pending"
         LIMIT 1
    ');
    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query changing_email_select.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        throw new Exception('Could not find a pending email change with that code.');
    }

    return $row;
}


function changing_emails_expire_old($pdo)
{
    $result = $pdo->exec('
        UPDATE changing_emails
           SET status = "expired",
               confirm_ip = "n/a"
         WHERE DATE(date) < DATE_SUB(NOW(), INTERVAL 1 DAY)
           AND status = "pending"
    ');

    if ($result === false) {
        throw new Exception('Could not expire old email changes.');
    }

    return $result;
}


function changing_emails_select_by_user($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
          SELECT change_id, old_email, new_email, code, date, request_ip, confirm_ip, status
            FROM changing_emails
           WHERE user_id = :user_id
        ORDER BY change_id ASC
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error === false) {
            throw new Exception('Could not perform query changing_emails_select_by_user.');
        } else {
            return false;
        }
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
