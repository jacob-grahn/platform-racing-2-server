<?php

function to_part_name($type, $id)
{
    // hats
    $hat_names_array = [
        2 => "EXP",
        3 => "Kong",
        4 => "Propeller",
        5 => "Cowboy",
        6 => "Crown",
        7 => "Santa",
        8 => "Party",
        9 => "Top",
        10 => "Jump-Start",
        11 => "Moon",
        12 => "Thief",
        13 => "Jigg",
        14 => "Artifact"
    ];

    // heads
    $head_names_array = [
        1 => "Classic",
        2 => "Tired",
        3 => "Smiler",
        4 => "Flower",
        5 => "Classic (Female)",
        6 => "Goof",
        7 => "Downer",
        8 => "Balloon",
        9 => "Worm",
        10 => "Unicorn",
        11 => "Bird",
        12 => "Sun",
        13 => "Candy",
        14 => "Invisible",
        15 => "Football Helmet",
        16 => "Basketball",
        17 => "Stick",
        18 => "Cat",
        19 => "Elephant",
        20 => "Ant",
        21 => "Astronaut",
        22 => "Alien",
        23 => "Dino",
        24 => "Armor",
        25 => "Fairy",
        26 => "Gingerbread",
        27 => "Bubble",
        28 => "Wise King",
        29 => "Wise Queen",
        30 => "Sir",
        31 => "Very Invisible",
        32 => "Taco",
        33 => "Slender",
        34 => "Santa",
        35 => "Frost Djinn",
        36 => "Reindeer",
        37 => "Crocodile",
        38 => "Valentine",
        39 => "Bunny"
    ];

    // bodies
    $body_names_array = [
        1 => "Classic",
        2 => "Strap",
        3 => "Dress",
        4 => "Pec",
        5 => "Gut",
        6 => "Collar",
        7 => "Miss PR2",
        8 => "Belt",
        9 => "Snake",
        10 => "Bird",
        11 => "Invisible",
        12 => "Bee",
        13 => "Stick",
        14 => "Cat",
        15 => "Car",
        16 => "Elephant",
        17 => "Ant",
        18 => "Astronaut",
        19 => "Alien",
        20 => "Galaxy",
        21 => "Bubble",
        22 => "Dino",
        23 => "Armor",
        24 => "Fairy",
        25 => "Gingerbread",
        26 => "Wise King",
        27 => "Wise Queen",
        28 => "Sir",
        29 => "Fred",
        30 => "Very Invisible",
        31 => "Taco",
        32 => "Slender",
        34 => "Santa",
        35 => "Frost Djinn",
        36 => "Reindeer",
        37 => "Crocodile",
        38 => "Valentine",
        39 => "Bunny"
    ];

    // feet
    $feet_names_array = [
        1 => "Classic",
        2 => "Heel",
        3 => "Loafer",
        4 => "Soccer",
        5 => "Magnet",
        6 => "Tiny",
        7 => "Sandal",
        8 => "Bare",
        9 => "Nice",
        10 => "Bird",
        11 => "Invisible",
        12 => "Stick",
        13 => "Cat",
        14 => "Car",
        15 => "Elephant",
        16 => "Ant",
        17 => "Astronaut",
        18 => "Alien",
        19 => "Galaxy",
        20 => "Dino",
        21 => "Armor",
        22 => "Fairy",
        23 => "Gingerbread",
        24 => "Wise King",
        25 => "Wise Queen",
        26 => "Sir",
        27 => "Very Invisible",
        28 => "Bubble",
        29 => "Taco",
        30 => "Slender",
        34 => "Santa",
        35 => "Frost Djinn",
        36 => "Reindeer",
        37 => "Crocodile",
        38 => "Valentine",
        39 => "Bunny"
    ];

    $lookup = [
        'hat' => $hat_names_array,
        'head' => $head_names_array,
        'body' => $body_names_array,
        'feet' => $feet_names_array
    ];

    return $lookup[$type][$id];
}

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
