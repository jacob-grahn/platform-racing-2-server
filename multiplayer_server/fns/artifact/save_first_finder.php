<?php

require_once __DIR__ . '/../../../http_server/queries/artifact_locations/artifact_location_update_first_finder.php';
require_once __DIR__ . '/../../../http_server/queries/artifact_locations/artifact_location_select.php';
require_once __DIR__ . '/../../../http_server/queries/messages/message_insert.php';

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

    // give all parts of the bubble set using the gain_part function from Player.php
    $player->gain_part("head", 27, true);
    $player->gain_part("body", 21, true);
    $player->gain_part("feet", 28, true);

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
        ."Congratulations for finding the artifact first! To commemorate this momentous occasion, you've been awarded with your very own bubble set.\n\n"
        ."Thanks for playing Platform Racing 2!\n\n"
        ."- Jiggmin";

    message_insert($pdo, $user_id, 1, $artifact_first_pm, '0');
}
