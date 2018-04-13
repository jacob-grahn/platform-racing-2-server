<?php

function validate_prize($type, $id)
{
    $type = htmlspecialchars(strtolower($type));
    $id = (int) $id;
    $type_array = array("hat", "head", "body", "feet", "ehat", "ehead", "ebody", "efeet");

    // check for a valid prize type
    if (!in_array($type, $type_array)) {
        throw new Exception("$type is an invalid prize type.");
    }

    // preserve epicness
    $is_epic = false;
    if ($type == 'ehat' || $type == 'ehead' || $type == 'ebody' || $type == 'efeet') {
        $is_epic = true;
    }

    // check for a valid hat id
    if ($type == "hat" || $type == "ehat") {
        $type = 'hat';
        if ($id < 2 || $id > 14) {
            throw new Exception("Invalid hat ID ($id) specified.");
        }
    }

    // check for a valid head id
    if ($type == "head" || $type == "ehead") {
        $type = 'head';
        if ($id < 1 || $id > 39) {
            throw new Exception("Invalid head ID ($id) specified.");
        }
    }

    // check for a valid body id
    if ($type == "body" || $type == "ebody") {
        $type = 'body';
        if ($id < 1 || $id > 39 || $id === 33) {
            throw new Exception("Invalid body ID ($id) specified.");
        }
    }

    // check for a valid feet id
    if ($type == "feet" || $type == "efeet") {
        $type = 'feet';
        if ($id < 1 || $id > 39 || ($id >= 31 && $id <= 33)) {
            throw new Exception("Invalid feet ID ($id) specified.");
        }
    }

    // if we got here, it means no exceptions were caught -- return our data
    $reply = new stdClass();
    $reply->type = $type;
    $reply->id = $id;
    $reply->epic = $is_epic;
    return $reply;
}
