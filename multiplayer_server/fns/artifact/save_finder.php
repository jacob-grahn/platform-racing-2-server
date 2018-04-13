<?php

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
