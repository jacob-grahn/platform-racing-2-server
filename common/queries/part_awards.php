<?php


/**
 * Deletes a part award according to passed user ID, part type, and part ID.
 *
 * @param resource pdo Database connection.
 * @param int user_id The user ID of the player.
 * @param string type The type of part to delete.
 * @param int id The ID of the part to delete.
 *
 * @throws Exception if the query fails.
 * @return bool
 */
function part_awards_delete($pdo, $user_id, $type, $id)
{
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('
        DELETE FROM
          part_awards
        WHERE
          user_id = :user_id
          AND type = :type
          AND part = :id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Could not delete a part award from the database.');
    }

    return $result;
}


function part_awards_delete_old($pdo)
{
    $result = $pdo->exec('
        DELETE FROM part_awards
         WHERE DATE_SUB(CURDATE(), INTERVAL 1 WEEK) > dateline
    ');

    if ($result === false) {
        throw new Exception('Could not delete old part awards.');
    }

    return $result;
}


function part_awards_insert($pdo, $user_id, $type, $part)
{
    $stmt = $pdo->prepare('
        INSERT INTO part_awards
           SET user_id = :user_id,
               type = :type,
               part = :part,
               dateline = NOW()
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':part', $part, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not insert part award.');
    }

    return $result;
}


function part_awards_select_list($pdo)
{
    $stmt = $pdo->prepare('
        SELECT user_id, type, part
          FROM part_awards
    ');
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not fetch the list of part awards.');
    }

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


function part_awards_select_by_user($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT type, part
          FROM part_awards
         WHERE user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not fetch the list of part awards.');
    }

    $awards = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($awards)) {
        return false;
    }

    return $awards;
}
