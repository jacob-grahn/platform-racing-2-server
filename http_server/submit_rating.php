<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/ratings.php';

$level_id = (int) default_post('level_id', 0);
$new_rating = (int) default_post('rating', 0);

$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

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
    if (is_empty($new_rating, false) || $new_rating < 1 || $new_rating > 5) {
        throw new Exception("Could not vote $new_rating.");
    }

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = token_login($pdo, false);

    // rate limiting
    rate_limit('submit-rating-'.$user_id, 5, 2);

    // see if they made the level
    if (level_check_if_creator($pdo, $user_id, $level_id) === true) {
        throw new Exception('You can\'t vote on yer own level, matey!');
    }

    // get their voting weight from their rank
    $rank = (int) pr2_select_true_rank($pdo, $user_id);
    $rank = $rank > 10 ? 10 : ($rank < 1 ? 1 : $rank);

    // see if they have voted on this level recently
    if (!empty(rating_select($pdo, $level_id, $user_id, $ip))) {
        throw new Exception('You have recently voted on this level. You can vote on it again in a week.');
    }

    // if they haven't voted, cast their vote
    rating_insert($pdo, $level_id, $new_rating, $user_id, $rank, $ip);

    // get the average rating and votes for some math
    $level = level_select($pdo, $level_id);
    $average_rating = (float) $level->rating;
    $votes = (int) $level->votes;

    // quick maths
    $total_rating = $average_rating * $votes;
    $total_rating += $rank * $new_rating;
    $votes += $rank;
    $new_average_rating = $votes <= 0 ? 0 : $total_rating / $votes;

    // if out of bounds, reset everything
    if ($new_average_rating > 5 or $new_average_rating < 1) {
        $new_average_rating = 0;
        $votes = 0;
    }

    // put the final average back into the level
    level_update_rating($pdo, $level_id, $new_average_rating, $votes);

    // define old and new ratings
    $old = round($average_rating, 2);
    $old = $old === 0 ? 'none' : $old;
    $new = round($new_average_rating, 2);

    // echo a message back
    $ret->success = true;
    $ret->message = "Thank you for voting! Your vote of $new_rating changed the average rating from $old to $new.";
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
