<?php


function rank_token_rental_insert($pdo, $user_id, $guild_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO rank_token_rentals
           SET user_id = :user_id,
               guild_id = :guild_id,
               time = UNIX_TIMESTAMP(NOW())
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not start your rank token rental.');
    }

    return $result;
}


function rank_token_rentals_count($pdo, $user_id, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*) AS count
        FROM rank_token_rentals
        WHERE
        (
          (
            guild_id = :guild_id
            AND guild_id != 0
          )
          OR user_id = :user_id
        )
        AND time > UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK)
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not count how many rank tokens you are currently renting.');
    }

    return (int) $stmt->fetchColumn();
}


function rank_token_rentals_select_next_expiry($pdo, $user_id, $guild_id)
{
    $stmt = $pdo->prepare('
        SELECT UNIX_TIMESTAMP(FROM_UNIXTIME(time) - INTERVAL 1 WEEK)
        FROM rank_token_rentals
        WHERE
        (
          (
            guild_id = :guild_id
            AND guild_id != 0
          )
          OR user_id = :user_id
        )
        AND time > UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK)
        ORDER BY time ASC
        LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select the next rank token expiry time.');
    }

    return (int) $stmt->fetchColumn();
}


function rank_token_rentals_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM rank_token_rentals
         WHERE time < UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK)
    ');

    if ($result === false) {
        throw new Exception('Could not delete expired rank token rentals.');
    }

    return $result;
}
