<?php

require_once __DIR__ . '/../../http_server/queries/artifacts_found/artifacts_found_insert.php';
require_once __DIR__ . '/../../http_server/queries/artifacts_found/artifacts_found_select_time.php';
require_once __DIR__ . '/../../http_server/queries/artifact_locations/artifact_location_update_first_finder.php';
require_once __DIR__ . '/../../http_server/queries/artifact_locations/artifact_location_select.php';
require_once __DIR__ . '/../../http_server/queries/messages/message_insert.php';

function has_found_artifact ($pdo, $player)
{
    $user_id = $player->user_id;

    try {
        // make sure they haven't found this artifact before
        $last_found_at = artifacts_found_select_time($pdo, $user_id);
        if ($last_found_at > Artifact::$updated_time) {
            return true;
        }

        return false;
    } catch (Exception $e) {
        $message = $e->getMessage();
        echo "Error: ".$message;
        return false;
    }
}
