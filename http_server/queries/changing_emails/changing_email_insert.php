<?php

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
