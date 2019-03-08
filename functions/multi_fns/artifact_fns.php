<?php


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
        if ((int) \pr2\multi\Artifact::$first_finder === 0 && $meets_rank_req && $meets_age_req) {
            save_first_finder($pdo, $player);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}


// save and award prizes to the first finder
function save_first_finder($pdo, $player)
{
    $user_id = (int) $player->user_id;

    artifact_location_update_first_finder($pdo, $user_id);
    $artifact = artifact_location_select($pdo);
    $first_finder = (int) $artifact->first_finder;

    // false alarm, someone else found it first
    if ($first_finder !== $user_id) {
        return;
    }

    // give all parts of the bubble set using the gainPart function from Player.php
    $player->gainPart("head", 27, true);
    $player->gainPart("body", 21, true);
    $player->gainPart("feet", 28, true);

    // don't go through the bubble set notification sequence if they already had it
    if ($player->head !== 27 && $player->body !== 21 && $player->feet !== 28) {
        return;
    }

    // tell the world
    $player->write('winPrize`' . json_encode(
        array(
        "type" => "eHead",
        "id" => 27,
        "name" => "Bubble Set!",
        "desc" => "For finding the artifact first, you earned your very own bubble set!",
        "universal" => true
        )
    ));

    // pm the user (finishing touch!)
    $html_user_name = htmlspecialchars($player->name);
    $artifact_first_pm = "Dear $html_user_name,\n\n"
        ."Congratulations for finding the artifact first! To commemorate this "
        ."momentous occasion, you've been awarded with your very own bubble set.\n\n"
        ."Thanks for playing Platform Racing 2!\n\n"
        ."- Jiggmin";

    message_insert($pdo, $user_id, 1, $artifact_first_pm, '0');
}
