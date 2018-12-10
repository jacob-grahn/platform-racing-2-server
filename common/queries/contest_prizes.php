<?php

function contest_prize_delete($pdo, $prize_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        DELETE FROM contest_prizes
        WHERE prize_id = :prize_id
    ');
    $stmt->bindValue(':prize_id', $prize_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        if ($suppress_error == false) {
            throw new Exception('Could not delete prize.');
        } else {
            return false;
        }
    }

    return true;
}


function contest_prize_insert($pdo, $contest_id, $part_type, $part_id)
{
    $part_type = strtolower($part_type);

    $stmt = $pdo->prepare('
        INSERT INTO contest_prizes
                SET contest_id = :contest_id,
                    part_type = :part_type,
                    part_id = :part_id,
                    added = :added
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_type', $part_type, PDO::PARAM_STR);
    $stmt->bindValue(':part_id', $part_id, PDO::PARAM_INT);
    $stmt->bindValue(':added', time(), PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not add prize to contest #$contest_id.");
    }

    // return last insert id
    return $pdo->lastInsertId();
}


function contest_prize_select_id($pdo, $contest_id, $part_type, $part_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT prize_id
          FROM contest_prizes
         WHERE contest_id = :contest_id
           AND part_type = :part_type
           AND part_id = :part_id
         LIMIT 1
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_type', $part_type, PDO::PARAM_STR);
    $stmt->bindValue(':part_id', $part_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select prize ID.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        if ($suppress_error == false) {
            throw new Exception("Could not find a prize row for contest "
                ."#$contest_id, part type \"$part_type\", and part id #$part_id.");
        } else {
            return false;
        }
    }

    return $row->prize_id;
}


function contest_prize_select($pdo, $prize_id)
{
    $stmt = $pdo->prepare('
        SELECT contest_id, part_type, part_id, added
          FROM contest_prizes
         WHERE prize_id = :prize_id
    ');
    $stmt->bindValue(':prize_id', $prize_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select prize ID.');
    }

    $prize = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($prize)) {
        throw new Exception("Could not find a prize #$prize_id.");
    }

    return $prize;
}


function contest_prizes_select_by_contest($pdo, $contest_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT prize_id, part_type, part_id
          FROM contest_prizes
         WHERE contest_id = :contest_id
    ');
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select contest prizes.');
    }

    $prizes = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($prizes)) {
        if ($suppress_error === false) {
            throw new Exception("No prizes found for contest #$contest_id.");
        } else {
            return false;
        }
    }

    return $prizes;
}
