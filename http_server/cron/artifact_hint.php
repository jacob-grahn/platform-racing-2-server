<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/PseudoRandom.php';
require_once __DIR__ . '/../queries/artifact_locations/artifact_location_select.php';
require_once __DIR__ . '/../queries/users/user_select.php';
require_once __DIR__ . '/../queries/levels/level_select.php';

$pdo = pdo_connect();

// collect data
$artifact = artifact_location_select($pdo);
$level_id = $artifact->level_id;
$updated_time = strtotime($artifact->updated_time);
$first_finder = $artifact->first_finder;

$level = level_select($pdo, $level_id);
$title = $level->title;
$user_id = $level->user_id;

$user = user_select($pdo, $user_id);
$user_name = $user->name;

if ($first_finder != 0) {
    $finder = user_select($pdo, $first_finder);
    $finder_name = $finder->name;
} else {
    $finder_name = '';
}


// form the base string we'll be creating
$str = "$title by $user_name";
$len = strlen($str);


// figure out how much of the string to reveal
$elapsed = time() - $updated_time;
$perc = $elapsed / (60*60*24*3);
if ($perc > 1) {
    $perc = 1;
}
$hide_perc = 1 - $perc;
$hide_characters = round($len * $hide_perc);
echo_line("hide_perc: $hide_perc");
echo_line("hide_characters: $hide_characters");
echo_line("len: $len");
echo_line("finder_name: $finder_name ");


//
\pr2\http\PseudoRandom::seed(112);


// replace a percentage of characters with underscores
$arr = str_split($str);
$loops = 0;
while ($hide_characters > 0) {
    $index = \pr2\http\PseudoRandom::num(0, $len-1);

    while ($arr[$index] == '_') {
        $index++;
        if ($index >= $len) {
            $index = 0;
        }

        $loops++;
        if ($loops > 100) {
            echo_line('infinite loop');
            break;
        }
    }
    $arr[ $index ] = '_';
    $hide_characters--;
}


// tell it to the world
$r = new stdClass();
$r->hint = join('', $arr);
$r->finder_name = $finder_name;
$r->updated_time = $updated_time;
$r_str = json_encode($r);

file_put_contents(__DIR__ . '/../www/files/artifact_hint.txt', $r_str);
output($r->hint);
