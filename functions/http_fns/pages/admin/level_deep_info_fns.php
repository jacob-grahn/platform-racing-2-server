<?php


function determine_song($num)
{
    if ($num == '' || $num == 'random') {
        return "Random";
    } else if ($num == '0' || $num == 'none') {
        return "None";
    }

    $num = (int) $num;
    $numArr = [
        "None",
        "Orbital Trance - Space Planet",
        "Code - Stefano Maccarelli",
        "Paradise on E - API",
        "Crying Soul (FL Mix) - Pyroific",
        "My Vision - David Orr",
        "Switchblade - Detective Jabsco",
        "The Wires - Cheez-R-Us",
        "Before Mydnite - F-777",
        "", // desert rose
        "Broked It - SWiTCH",
        "Hello? - TMM43",
        "Pyrokinesis - Sean Tucker",
        "Flowerz 'n' Herbz - Brunzolaitis",
        "Instrumental #4 - Reasoner",
        "Prismatic - Lunanova",
        "We Are Loud - Dynamedion", // should never be used; song can't be set to we are loud
        "Toodaloo - mustangman",
        "Night Shade - Goliathe",
        "Blizzard! - Majicke"
    ];
    return $numArr[$num];
}


function determine_mode($type)
{
    if ($type == 'deathmatch' || $type == 'dm' || $type == 'd') {
        $type = 'Deathmatch';
    } else if ($type == 'egg' || $type == 'eggs' || $type == 'e') {
        $type = 'Alien Eggs';
    } else if ($type == 'objective' || $type == 'obj' || $type == 'o') {
        $type = 'Objective';
    } else if ($type == 'hat' || $type == 'h') {
        $type = 'Hat Attack';
    } else {
        $type = 'Race';
    }
    return $type;
}


function output_objects($objs)
{
    if ($objs !== false) {
        foreach ($objs as $obj) {
            output_object($obj, ', ');
            echo '<br/>';
        }
    }
}


function output_object($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($var === 'user_id' && $obj->author !== false) {
                if ($obj->author !== false) {
                    $val = $obj->author;
                    unset($obj->author);
                    $safe_val = htmlspecialchars($val, ENT_QUOTES);
                    $url_val = urlencode($val);
                    $val = "<a href='player_deep_info.php?name1=$url_val'>$safe_val</a>";
                    echo "author: $val$sep";
                    continue;
                }
                unset($obj->author);
                echo "$var: $val <i>(user no longer exists)</i>$sep";
                continue;
            } elseif ($var === 'ip') {
                $safe_ip = htmlspecialchars($val, ENT_QUOTES);
                $url_ip = urlencode($val);
                $val = "<a href='/mod/ip_info.php?ip=$url_ip'>$safe_ip</a>";
                echo "$var: $val$sep";
                continue;
            } elseif ($var === 'time') {
                $val = date('M j, Y g:i A', $val) . " ($val)";
            } elseif ($var === 'song') {
                $val = determine_song($val);
            } elseif ($var === 'type') {
                $val = determine_mode($val);
            }
            if ($var === 'version' || $var === 'votes' || $var === 'play_count') {
                $val = number_format((int) $val);
            }
            if ($var !== 'level_id') {
                echo "$var: ".htmlspecialchars($val, ENT_QUOTES)."$sep";
            }
        }
    }
}
