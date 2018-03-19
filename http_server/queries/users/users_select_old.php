<?php

function users_select_old($pdo)
{
    $year3 = time() - 94610000; // 3 years
    $month = time() - 2592000; // 1 month

    $stmt = $pdo->prepare('
               SELECT users.user_id,
                      users.time,
                      pr2.rank,
                      pr2.user_id
                 FROM users,
                      pr2
               WHERE (users.time < :year3 AND users.user_id = pr2.user_id AND pr2.rank < 15) /* users that meet deletion criteria */
                  OR (users.time < :month AND users.user_id NOT IN (SELECT user_id FROM pr2)) /* users that do not have pr2 records after a month */
    ');
    $stmt->bindValue(':year3', $year3, PDO::PARAM_INT);
    $stmt->bindValue(':month', $month, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to select old users');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
