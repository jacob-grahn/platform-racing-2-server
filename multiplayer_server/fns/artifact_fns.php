<?php


// checks if a player has already found this artifact
function has_found_artifact($pdo, $player)
{
    $user_id = $player->user_id;

    try {
        // make sure they haven't found this artifact before
        $last_found_at = artifacts_found_select_time($pdo, $user_id);
        if ($last_found_at > \pr2\multi\Artifact::$updated_time) {
            return true;
        }

        return false;
    } catch (Exception $e) {
        $message = $e->getMessage();
        echo "Error: ".$message;
        return false;
    }
}


// save record of a player getting this artifact
function save_finder($pdo, $player)
{
    try {
        // does not count if you have found this artifact already
        if (has_found_artifact($pdo, $player)) {
            return false;
        }

        // yay, record that you found it
        artifacts_found_insert($pdo, $player->user_id);

        // check if you were the very first
        if (!\pr2\multi\Artifact::$first_finder) {
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
    $user_id = $player->user_id;

    artifact_location_update_first_finder($pdo, $user_id);
    $artifact = artifact_location_select($pdo);
    $first_finder = $artifact->first_finder;

    // false alarm, someone else found it first
    if ($first_finder !== $user_id) {
        return;
    }

    // give all parts of the bubble set using the gainPart function from Player.php
    $player->gainPart("head", 27, true);
    $player->gainPart("body", 21, true);
    $player->gainPart("feet", 28, true);

    // tell the world
    $player->write('winPrize`' . json_encode(
        array(
        "type" => "eHead",
        "id" => 27,
        "name" => "Bubble Set",
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
