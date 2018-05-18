<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/levels/level_check_if_creator.php';
require_once QUERIES_DIR . '/levels/level_select.php';
require_once QUERIES_DIR . '/levels/level_update_rating.php';
require_once QUERIES_DIR . '/ratings/rating_insert.php';
require_once QUERIES_DIR . '/ratings/rating_select.php';

$time = (int) time();
$level_id = (int) $_POST['level_id'];
$old_weight = 0;
$weight = 1;
$old_rating = 0;
$new_rating = (int) $_POST['rating'];

$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }
    
    // ref check
    require_trusted_ref('rate levels');

    // rate limiting
    rate_limit('submit-rating-'.$ip, 5, 2);

    // sanity check: is the rating valid?
    $new_rating = round($new_rating);
    if (is_nan($new_rating) || $new_rating < 1 || $new_rating > 5) {
        throw new Exception("Could not vote $new_rating.");
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // rate limiting
    rate_limit('submit-rating-'.$user_id, 5, 2);

    // see if they made the level
    $is_creator = level_check_if_creator($pdo, $user_id, $level_id);
    if ($is_creator === true) {
        throw new Exception("You can't vote on yer own level, matey!");
    }

    // get their voting weight
    $user_pr2 = pr2_select($pdo, $user_id);
    $weight = $user_pr2->rank;
    if ($weight > 10) {
        $weight = 10;
    }
    if ($weight < 1) {
        $weight = 1;
    }

    // see if they have voted on this level recently
    $prev_vote = rating_select($pdo, $level_id, $user_id, $ip);
    if ($prev_vote) {
        throw new Exception('You have recently voted on this level. You can vote on it again in a week.');
    }

    // if they haven't voted, cast their vote
    rating_insert($pdo, $level_id, $new_rating, $user_id, $weight, $time, $ip);

    // get the average rating and votes for some math
    $level = level_select($pdo, $level_id);
    $average_rating = $level->rating;
    $votes = $level->votes;

    // quick maths
    $total_rating = $average_rating * $votes;
    $total_rating -= $weight * $old_rating;
    $total_rating += $weight * $new_rating;
    $votes += $weight - $old_weight;
    if ($votes <= 0) {
        $new_average_rating = 0;
    } else {
        $new_average_rating = $total_rating / $votes;
    }

    if ($new_average_rating > 5) {
        $new_average_rating = 0;
        $votes = 0;
    }

    // put the final average back into the level
    if (!is_nan($new_average_rating)) {
        level_update_rating($pdo, $level_id, $new_average_rating, $votes);
    }

    // echo a message back
    echo 'message=Thank you for voting! ';
    $old = round($average_rating, 2);
    $new = round($new_average_rating, 2);
    if ($old == 0) {
        $old = 'none';
    }
    if ($old_rating == 0) {
        echo "Your vote of $new_rating changed the average rating from $old to $new.";
    } else {
        echo "You changed your vote from $old_rating to $new_rating, "
            ."which changed the average rating from $old to $new.";
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
