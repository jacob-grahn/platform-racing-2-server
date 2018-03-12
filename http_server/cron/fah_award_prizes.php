<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/folding/folding_insert.php';
require_once __DIR__ . '/../queries/folding/folding_select_by_id.php';
require_once __DIR__ . '/../queries/folding/folding_select_list.php';
require_once __DIR__ . '/../queries/folding/folding_update.php';
require_once __DIR__ . '/../queries/messages/messages_insert.php';
require_once __DIR__ . '/../queries/rank_tokens/rank_token_upsert.php';

$prize_array = array();
$processed_names = array();
$name_switch_array = array();


// connect to the db
$fah_db = new DB(fah_connect());
$pr2_db = new DB();
$pdo = pdo_connect();



// create a list of existing users and their prizes
$folding_rows = folding_select_list($pdo);
foreach ($folding_rows as $row) {
    $prize_array[strtolower($row->name)] = $row;
}


// create a list of name switches
$result = $fah_db->call('pr2_name_links_select');
while ($row = $result->fetch_object()) {
    $name_switch_array[strtolower($row->fah_name)] = strtolower($row->pr2_name);
}



// get fah user stats
$result = $fah_db->call('stats_select_all');
while ($user = $result->fetch_object()) {
    add_prizes($pdo, $user->fah_name, $user->points, $prize_array, $processed_names);
}



function add_prizes($pdo, $name, $score, $prize_array, $processed_names)
{
    $name = replace_name($name);
    $lower_name = strtolower($name);

    if (!isset($processed_names[$lower_name])) {
        $processed_names[$lower_name] = 1;

        try {
            if (isset($prize_array[$lower_name])) {
                $row = $prize_array[$lower_name];
                $user_id = $row->user_id;
                $status = $row->status;
            } else {
                $user = user_select_by_name($pdo, $name);

                // make some variables
                $user_id = $user->user_id;
                $status = $user->status;

                folding_insert($pdo, $user_id);
                $row = folding_select_by_user_id($pdo, $user_id);
            }

            if ($status != 'offline') {
                throw new Exception("$name is \"$status\". Abort mission! We'll try again later.");
            }

            //3 rank in pr2
            award_prize($pdo, $user_id, $name, $score, $row, 'r1', 1, '+1 rank token in Platform Racing 2');
            award_prize($pdo, $user_id, $name, $score, $row, 'r2', 500, '+1 rank token in Platform Racing 2');
            award_prize($pdo, $user_id, $name, $score, $row, 'r3', 1000, '+1 rank token in Platform Racing 2');

            //crown hat
            award_prize($pdo, $user_id, $name, $score, $row, 'crown_hat', 5000, 'Crown Hat in Platform Racing 2');

            //cowboy hat
            award_prize($pdo, $user_id, $name, $score, $row, 'cowboy_hat', 100000, 'Super Flying Cowboy Hat in Platform Racing 2');

            //some more rank tokens
            award_prize($pdo, $user_id, $name, $score, $row, 'r4', 1000000, '+1 rank increase in Platform Racing 2');
            award_prize($pdo, $user_id, $name, $score, $row, 'r5', 10000000, '+1 rank increase in Platform Racing 2');
        } catch (Exception $e) {
            $error = $e->getMessage();
            $safe_error = htmlspecialchars($error);
            output($safe_error);
        }
    }
}



function award_prize($pdo, $user_id, $name, $score, $row, $column_name, $min_score, $prize_str)
{
    global $pr2_db;
    if ($score >= $min_score && $row->{$column_name} != 1) {
        output("awarding $column_name to $name");
        $row->{$column_name} = 1;

        //give the prize
        if ($column_name == 'r1' || $column_name == 'r2' || $column_name == 'r3' || $column_name == 'r4' || $column_name == 'r5') {
            if ($column_name == 'r1') {
                $tokens = 1;
            } elseif ($column_name == 'r2') {
                $tokens = 2;
            } elseif ($column_name == 'r3') {
                $tokens = 3;
            } elseif ($column_name == 'r4') {
                $tokens = 4;
            } elseif ($column_name == 'r5') {
                $tokens = 5;
            }
            rank_token_upsert($pdo, $user_id, $tokens);
        } elseif ($column_name == 'crown_hat') {
            $parts = array();
            $parts[] = 6;
            award_parts($pr2_db, $user_id, 'hat', $parts);
        } elseif ($column_name == 'cowboy_hat') {
            $parts = array();
            $parts[] = 5;
            award_parts($pr2_db, $user_id, 'hat', $parts);
        }

        //send them a PM
        $message = "$name, congratulations on earning $min_score points for Team Jiggmin! You have been awarded with a $prize_str. \n\nThanks for helping us take over the world! (or cure cancer)\n\n- Jiggmin";
        message_insert($pdo, $user_id, 1, $message, '0');

        //remember that this prize has been given
        folding_update($pdo, $user_id, $column_name);
    }
}



function replace_name($name)
{
    global $name_switch_array;
    $name = str_replace('_', ' ', $name);
    if (isset($name_switch_array[strtolower($name)])) {
        $new_name = $name_switch_array[strtolower($name)];
        output("replacing $name with $new_name");
        $name = $new_name;
    }
    return $name;
}






// handy output function; never leave home without it!
function output($str)
{
    echo("* $str \n");
}
