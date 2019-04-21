<?php


// save record of a player getting this artifact
function save_finder($pdo, $player)
{
    try {
        // does not count if you have found this artifact already
        if (has_found_artifact($pdo, $player) === true) {
            return false;
        }

        // yay, record that you found it
        artifacts_found_insert($pdo, $player->user_id);

        // check if you were the very first
        $meets_rank_req = $player->active_rank > 30 ? true : false;
        $meets_age_req = time() - $player->register_time > 7776000 ? true : false;
        if ($meets_rank_req && $meets_age_req) {
            artifact_special_check($pdo, $player);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}


// checks if a player has already found this artifact
function has_found_artifact($pdo, $player)
{
    try {
        // make sure they haven't found this artifact before
        $last_found_at = artifacts_found_select_time($pdo, $player->user_id);
        return $last_found_at > \pr2\multi\Artifact::$updated_time || $player->group <= 0;
    } catch (Exception $e) {
        $error = $e->getMessage();
        echo "Error: $error";
        return false;
    }
}


// save the first finder and/or award bubbles
function artifact_special_check($pdo, $player)
{
    $user_id = (int) $player->user_id;
    $has_bubbles = has_bubbles($player);
    $first_finder = (int) \pr2\multi\Artifact::$first_finder;
    $bubbles_winner = (int) \pr2\multi\Artifact::$bubbles_winner;

    // check if first finder
    if ($first_finder === 0) {
        save_first_finder($pdo, $user_id, $has_bubbles);
        $first_finder = (int) \pr2\multi\Artifact::$first_finder;
    }

    // check if bubbles are still up for grabs
    if ($bubbles_winner === 0 && $has_bubbles === false) {
        save_bubbles_winner($pdo, $user_id, $first_finder);
    }
}


// check if the player has the bubble set
function has_bubbles($player)
{
    $hasHead = $player->hasPart('head', 27);
    $hasBody = $player->hasPart('body', 21);
    $hasFeet = $player->hasPart('feet', 28);
    if (!$hasHead || !$hasBody || !$hasFeet) {
        return false;
    } else {
        return true;
    }
}


// save the first finder
function save_first_finder($pdo, $user_id, $has_bubbles)
{
    artifact_location_update_first_finder($pdo, $user_id);
    artifacts_found_increment_first_count($pdo, $user_id);
    \pr2\multi\Artifact::$first_finder = $user_id;

    // if they have bubbles, give them 10,000 EXP (if they don't, it'll trigger the bubble winner sequence)
    if ($has_bubbles === true) {
        $desc = "You found the artifact first! Since you have the bubble set, here's a 10,000 EXP bonus instead!";
        $player->write('winPrize`' . json_encode(
            array(
                "type" => "exp",
                "id" => 10000,
                "name" => "EXP Bonus",
                "desc" => $desc
            )
        ));
    }
}


// save the bubbles winner
function save_bubbles_winner($pdo, $user_id, $first_finder)
{
    artifact_location_update_bubbles_winner($pdo, $user_id);
    \pr2\multi\Artifact::$bubbles_winner = $user_id;

    // award the bubble set
    $player->gainPart("head", 27, true);
    $player->gainPart("body", 21, true);
    $player->gainPart("feet", 28, true);

    // determine message, depending on if finding first
    if ($first_finder === $user_id) {
        $desc = "For finding the artifact first, you earned your very own bubble set!";
        $pm_lang = 'Congratulations on finding the artifact first! '
            .'To commemorate this momentous occasion, you\'ve been awarded with your very own bubble set.';
    } else {
        $desc = "The first finder of the artifact already had this prize, so these bubbles are yours!";
        $pm_lang = 'Congratulations on finding the artifact! '
            .'You were the first person to find it who didn\'t own the bubble set, so the set has been awarded to you.';
    }

    // send a prize popup
    $player->write('winPrize`' . json_encode(
        array(
            "type" => "eHead",
            "id" => 27,
            "name" => "Bubble Set",
            "desc" => $desc,
        )
    ));

    // pm the user (finishing touch!)
    $html_user_name = htmlspecialchars($player->name);
    $artifact_first_pm = "Dear $html_user_name,\n\n$pm_lang\n\nThanks for playing Platform Racing 2!\n\n- Jiggmin";
    message_insert($pdo, $user_id, 1, $artifact_first_pm, '0');
}
