<?php

require_once __DIR__ . '/has_found_artifact.php';
require_once __DIR__ . '/save_first_finder.php';
require_once __DIR__ . '/../../../http_server/queries/artifacts_found/artifacts_found_insert.php';

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
        if (!Artifact::$first_finder) {
            save_first_finder($pdo, $player);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}
