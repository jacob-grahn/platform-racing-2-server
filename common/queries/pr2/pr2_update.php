<?php

function pr2_update(
    $pdo,
    $user_id,
    $rank,
    $exp_points,
    $hat_color,
    $head_color,
    $body_color,
    $feet_color,
    $hat_color_2,
    $head_color_2,
    $body_color_2,
    $feet_color_2,
    $hat,
    $head,
    $body,
    $feet,
    $hat_array,
    $head_array,
    $body_array,
    $feet_array,
    $speed,
    $acceleration,
    $jumping
) {
    $stmt = $pdo->prepare('
        UPDATE pr2
        SET rank = :rank,
            exp_points = :exp_points,
            hat_color = :hat_color,
            head_color = :head_color,
            body_color = :body_color,
            feet_color = :feet_color,
            hat_color_2 = :hat_color_2,
            head_color_2 = :head_color_2,
            body_color_2 = :body_color_2,
            feet_color_2 = :feet_color_2,
            hat = :hat,
            head = :head,
            body = :body,
            feet = :feet,
            hat_array = :hat_array,
            head_array = :head_array,
            body_array = :body_array,
            feet_array = :feet_array,
            speed = :speed,
            acceleration = :acceleration,
            jumping = :jumping
        WHERE user_id = :user_id
        AND rank <= :rank
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':rank', $rank, PDO::PARAM_INT);
    $stmt->bindValue(':exp_points', $exp_points, PDO::PARAM_INT);

    $stmt->bindValue(':hat_color', $hat_color, PDO::PARAM_INT);
    $stmt->bindValue(':head_color', $head_color, PDO::PARAM_INT);
    $stmt->bindValue(':body_color', $body_color, PDO::PARAM_INT);
    $stmt->bindValue(':feet_color', $feet_color, PDO::PARAM_INT);

    $stmt->bindValue(':hat_color_2', $hat_color_2, PDO::PARAM_INT);
    $stmt->bindValue(':head_color_2', $head_color_2, PDO::PARAM_INT);
    $stmt->bindValue(':body_color_2', $body_color_2, PDO::PARAM_INT);
    $stmt->bindValue(':feet_color_2', $feet_color_2, PDO::PARAM_INT);

    $stmt->bindValue(':hat', $hat, PDO::PARAM_INT);
    $stmt->bindValue(':head', $head, PDO::PARAM_INT);
    $stmt->bindValue(':body', $body, PDO::PARAM_INT);
    $stmt->bindValue(':feet', $feet, PDO::PARAM_INT);

    $stmt->bindValue(':hat_array', $hat_array, PDO::PARAM_STR);
    $stmt->bindValue(':head_array', $head_array, PDO::PARAM_STR);
    $stmt->bindValue(':body_array', $body_array, PDO::PARAM_STR);
    $stmt->bindValue(':feet_array', $feet_array, PDO::PARAM_STR);

    $stmt->bindValue(':speed', $speed, PDO::PARAM_INT);
    $stmt->bindValue(':acceleration', $acceleration, PDO::PARAM_INT);
    $stmt->bindValue(':jumping', $jumping, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not perform query to update pr2 row.');
    }

    return $result;
}
