<?php

// hats
$hat_2 = "EXP";
$hat_3 = "Kong";
$hat_4 = "Propeller";
$hat_5 = "Cowboy";
$hat_6 = "Crown";
$hat_7 = "Santa";
$hat_8 = "Party";
$hat_9 = "Top";
$hat_10 = "Jump-Start";
$hat_11 = "Moon";
$hat_12 = "Thief";
$hat_13 = "Jigg";
$hat_14 = "Artifact";

// heads
$head_1 = "Classic";
$head_2 = "Tired";
$head_3 = "Smiler";
$head_4 = "Flower";
$head_5 = "Classic (Female)";
$head_6 = "Goof";
$head_7 = "Downer";
$head_8 = "Balloon";
$head_9 = "Worm";
$head_10 = "Unicorn";
$head_11 = "Bird";
$head_12 = "Sun";
$head_13 = "Candy";
$head_14 = "Invisible";
$head_15 = "Football Helmet";
$head_16 = "Basketball";
$head_17 = "Stick";
$head_18 = "Cat";
$head_19 = "Elephant";
$head_20 = "Ant";
$head_21 = "Astronaut";
$head_22 = "Alien";
$head_23 = "Dino";
$head_24 = "Armor";
$head_25 = "Fairy";
$head_26 = "Gingerbread";
$head_27 = "Bubble";
$head_28 = "Wise King";
$head_29 = "Wise Queen";
$head_30 = "Sir";
$head_31 = "Very Invisible";
$head_32 = "Taco";
$head_33 = "Slender";
$head_34 = "Santa";
$head_35 = "Frost Djinn";
$head_36 = "Reindeer";
$head_37 = "Crocodile";
$head_38 = "Valentine";
$head_39 = "Bunny";

// bodies
$body_1 = "Classic";
$body_2 = "Strap";
$body_3 = "Dress";
$body_4 = "Pec";
$body_5 = "Gut";
$body_6 = "Collar";
$body_7 = "Miss PR2";
$body_8 = "Belt";
$body_9 = "Snake";
$body_10 = "Bird";
$body_11 = "Invisible";
$body_12 = "Bee";
$body_13 = "Stick";
$body_14 = "Cat";
$body_15 = "Car";
$body_16 = "Elephant";
$body_17 = "Ant";
$body_18 = "Astronaut";
$body_19 = "Alien";
$body_20 = "Galaxy";
$body_21 = "Bubble";
$body_22 = "Dino";
$body_23 = "Armor";
$body_24 = "Fairy";
$body_25 = "Gingerbread";
$body_26 = "Wise King";
$body_27 = "Wise Queen";
$body_28 = "Sir";
$body_29 = "Fred";
$body_30 = "Very Invisible";
$body_31 = "Taco";
$body_32 = "Slender";
$body_34 = "Santa";
$body_35 = "Frost Djinn";
$body_36 = "Reindeer";
$body_37 = "Crocodile";
$body_38 = "Valentine";
$head_39 = "Bunny";

// feet
$feet_1 = "Classic";
$feet_2 = "Heel";
$feet_3 = "Loafer";
$feet_4 = "Soccer";
$feet_5 = "Magnet";
$feet_6 = "Tiny";
$feet_7 = "Sandal";
$feet_8 = "Bare";
$feet_9 = "Nice";
$feet_10 = "Bird";
$feet_11 = "Invisible";
$feet_12 = "Stick";
$feet_13 = "Cat";
$feet_14 = "Car";
$feet_15 = "Elephant";
$feet_16 = "Ant";
$feet_17 = "Astronaut";
$feet_18 = "Alien";
$feet_19 = "Galaxy";
$feet_20 = "Dino";
$feet_21 = "Armor";
$feet_22 = "Fairy";
$feet_23 = "Gingerbread";
$feet_24 = "Wise King";
$feet_25 = "Wise Queen";
$feet_26 = "Sir";
$feet_27 = "Very Invisible";
$feet_28 = "Bubble";
$feet_29 = "Taco";
$feet_30 = "Slender";
$feet_34 = "Santa";
$feet_35 = "Frost Djinn";
$feet_36 = "Reindeer";
$feet_37 = "Crocodile";
$feet_38 = "Valentine";
$feet_39 = "Bunny";

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
