<?php


function admin_pr2_update($pdo, $user_id, $hats, $heads, $bodies, $feet)
{
    $stmt = $pdo->prepare('
        UPDATE pr2
           SET hat_array = :hats,
               head_array = :heads,
               body_array = :bodies,
               feet_array = :feet
         WHERE user_id = :user_id
        ');
    $stmt->bindValue(':hats', $hats, PDO::PARAM_STR);
    $stmt->bindValue(':heads', $heads, PDO::PARAM_STR);
    $stmt->bindValue(':bodies', $bodies, PDO::PARAM_STR);
    $stmt->bindValue(':feet', $feet, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Could not update PR2 data.");
    }

    return true;
}


function pr2_insert($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO pr2
           SET user_id = :user_id
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query to insert PR2 player data.');
    }

    return $result;
}


function pr2_select_rank_progress($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT
          pr2.exp_points AS exp,
          (pr2.rank + IFNULL(rt.used_tokens, 0)) AS "rank",
          IFNULL(rt.used_tokens, 0) AS tokens
        FROM
          pr2
        LEFT JOIN
          rank_tokens rt ON rt.user_id = pr2.user_id
        WHERE
          pr2.user_id = :user_id
        LIMIT
          1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result == false) {
        throw new Exception("Could not perform query pr2_select_rank_progress.");
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}


function pr2_select_true_rank($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT pr2.rank AS "rank",
               rank_tokens.used_tokens AS tokens
          FROM pr2
          LEFT JOIN rank_tokens ON rank_tokens.user_id = pr2.user_id
         WHERE pr2.user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result == false) {
        throw new Exception("Could not perform query pr2_select_true_rank.");
    }

    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if ($result === false || empty($result)) {
        throw new Exception("Could not find a user with that ID.");
    }

    $rank = (int) $result->rank;
    $tokens = (int) $result->tokens;
    $true_rank = $rank + $tokens;

    return $true_rank;
}


function pr2_select($pdo, $user_id, $suppress_error = false)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM pr2
         WHERE user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query pr2_select.');
    }

    $pr2 = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($pr2)) {
        if ($suppress_error === false) {
            throw new Exception('Could not find PR2 player data for this user.');
        } else {
            return false;
        }
    }

    return $pr2;
}


function pr2_update_part_array($pdo, $user_id, $type, $part_array)
{
    switch ($type) {
        case 'hat':
            $field = 'hat_array';
            break;
        case 'head':
            $field = 'head_array';
            break;
        case 'body':
            $field = 'body_array';
            break;
        case 'feet':
            $field = 'feet_array';
            break;
        default:
            throw new Exception('Unknown part type.');
    }

    $stmt = $pdo->prepare("
        UPDATE pr2
           SET $field = :part_array
         WHERE user_id = :user_id
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':part_array', $part_array, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result === false) {
        $user_id = (int) $user_id;
        throw new Exception("Could not update user #$user_id's PR2 player data on column $field.");
    }

    return $result;
}


function pr2_update_rank($pdo, $user_id, $new_rank, $exp_points = null)
{
    $exp_str = $exp_points !== null ? 'exp_points = :exp_points,' : '';
    $stmt = $pdo->prepare("
        UPDATE
          pr2
        SET
          $exp_str
          rank = :rank
        WHERE
          user_id = :user_id
          AND rank <= :rank
        LIMIT
          1
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':rank', $new_rank, PDO::PARAM_INT);
    if ($exp_points !== null) {
        $stmt->bindValue(':exp_points', $exp_points, PDO::PARAM_INT);
    }
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not perform query pr2_update_rank.');
    }

    return $result;
}


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
          SET pr2.rank = :rank,
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
          AND pr2.rank <= :rank
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
