<?php

function stats_insert($pdo, $name, $wu, $points, $rank)
{
    $stmt = $pdo->prepare('
        DECLARE _last_wu INT(11) DEFAULT 0;
        DECLARE _last_points INT(11) DEFAULT 0;
        DECLARE _wu_gain INT(11) DEFAULT 0;
        DECLARE _point_gain INT(11) DEFAULT 0;

        SELECT wu, points INTO _last_wu, _last_points
        FROM stats
        WHERE fah_name = :name
        LIMIT 0, 1;

        SET _wu_gain = :wu - _last_wu;
        SET _point_gain = :points - _last_points;

        INSERT INTO stats
        SET fah_name = :name,
                wu = :wu,
                points = :points,
                rank = :rank
        ON DUPLICATE KEY UPDATE
                wu = :wu,
                points = :points,
                rank = :rank;

        IF (_wu_gain + _point_gain > 0) THEN
            INSERT INTO gains
            SET fah_name = :name,
                    wu = _wu_gain,
                    points = _point_gain,
                    date = NOW();
        END IF;

        SELECT _wu_gain, _point_gain;
    ');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':wu', $wu, PDO::PARAM_INT);
    $stmt->bindValue(':points', $points, PDO::PARAM_INT);
    $stmt->bindValue(':rank', $rank, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not update fah stats');
    }

    return $result;
}
