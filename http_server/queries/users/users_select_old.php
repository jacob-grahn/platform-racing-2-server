<?php

function users_select_old($pdo, $min_time)
{
	$stmt = $pdo->prepare('
        SELECT users.user_id, pr2.rank
        FROM users, pr2
        WHERE users.time < :min_time
        AND users.user_id = pr2.user_id
        AND pr2.rank < 15
    ');
    $stmt->bindValue(':min_time', $min_time, PDO::PARAM_INT);
	$result = $stmt->execute();

    if (!$result) {
        throw new Exception('user not found');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
