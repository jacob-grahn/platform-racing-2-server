<?php

require_once __DIR__ . '/../fns/all_fns.php';

$prize_array = array();
$processed_names = array();
$name_switch_array = array();


//--- connect to the db ----------------------------------------------------------------------------------
$fah_db = new DB(fah_connect());
$pr2_db = new DB();



//--- create a list of existing users and their prizes --------------------------------------------------
$result = $pr2_db->query(
    'SELECT folding_at_home.*, users.name, users.status
							 	FROM folding_at_home, users
								WHERE folding_at_home.user_id = users.user_id'
);

while($row = $result->fetch_object()) {
    $prize_array[strtolower($row->name)] = $row;
}



//--- create a list of name switches --------------------------------------------------------------------
$result = $fah_db->call('pr2_name_links_select');
while($row = $result->fetch_object()) {
    $name_switch_array[strtolower($row->fah_name)] = strtolower($row->pr2_name);
}



//--- get fah user stats -------------------------------------------------------------------------------
$result = $fah_db->call('stats_select_all');
while($user = $result->fetch_object()) {
    add_prizes($pr2_db, $user->fah_name, $user->points, $prize_array, $processed_names);
}



function add_prizes($db, $name, $score, $prize_array, $processed_names) 
{
    $name = replace_name($name);
    $lower_name = strtolower($name);

    if(!isset($processed_names[$lower_name])) {
        $processed_names[$lower_name] = 1;

        try {

            if(isset($prize_array[$lower_name])) {
                $row = $prize_array[$lower_name];
                $user_id = $row->user_id;
                $status = $row->status;
            }

            else {
                // convert name to user ID
                $user_id = name_to_id($db, $name);
                $user = $db->grab_row('user_select', array($user_id), 'Could not find a user with that ID.');
                
                // make some variables
                $user_id = (int) $user->user_id;
                $status = $user->status;

                // grab their F@H record
                $record = $db->query(
                    "SELECT * 
								FROM folding_at_home
								WHERE user_id = '$user_id'
								LIMIT 0, 1"
                );
                if(!$record) {
                    throw new Exception("Could not retrieve $name's F@H record.");
                }
                if($record->num_rows <= 0) {
                    // create a new F@H record for them if they don't have one
                    $add_result = $db->query("INSERT INTO folding_at_home SET user_id = '$user_id'");
                    if(!$add_result) {
                        throw new Exception("Could not create $name's F@H record.");
                    }
                    $message = "Welcome to Team Jiggmin, $name! Your help in taking over the world (or curing cancer) is much appreciated! \n\n- Jiggmin";
                    $db->call('message_insert', array($user_id, 1, $message, '0'));
                    throw new Exception("Successfully created $name's F@H record.");
                }
                $row = $record->fetch_object();
            }

            if($status != 'offline') {
                throw new Exception("$name is \"$status\". Abort mission! We'll try again later.");
            }

            //3 rank in pr2
            award_prize($db, $user_id, $name, $score, $row, 'r1', 1, '+1 rank token in Platform Racing 2');
            award_prize($db, $user_id, $name, $score, $row, 'r2', 500, '+1 rank token in Platform Racing 2');
            award_prize($db, $user_id, $name, $score, $row, 'r3', 1000, '+1 rank token in Platform Racing 2');

            //crown hat
            award_prize($db, $user_id, $name, $score, $row, 'crown_hat', 5000, 'Crown Hat in Platform Racing 2');

            //cowboy hat
            award_prize($db, $user_id, $name, $score, $row, 'cowboy_hat', 100000, 'Super Flying Cowboy Hat in Platform Racing 2');

            //some more rank tokens
            award_prize($db, $user_id, $name, $score, $row, 'r4', 1000000, '+1 rank increase in Platform Racing 2');
            award_prize($db, $user_id, $name, $score, $row, 'r5', 10000000, '+1 rank increase in Platform Racing 2');

        }
        catch(Exception $e) {
            $error = $e->getMessage();
            $safe_error = htmlspecialchars($error);
            output($safe_error);
        }
    }
}



function award_prize($db, $user_id, $name, $score, $row, $db_val, $min_score, $prize_str)
{
    if($score >= $min_score && $row->{$db_val} != 1) {

        output("awarding $db_val to $name");
        $row->{$db_val} = 1;

        //give the prize
        $int_user_id = (int) $user_id;
        if($db_val == 'r1' || $db_val == 'r2' || $db_val == 'r3' || $db_val == 'r4' || $db_val == 'r5') {
            if($db_val == 'r1') {
                $tokens = 1;
            }
            else if($db_val == 'r2') {
                $tokens = 2;
            }
            else if($db_val == 'r3') {
                $tokens = 3;
            }
            else if($db_val == 'r4') {
                $tokens = 4;
            }
            else if($db_val == 'r5') {
                $tokens = 5;
            }
            $result = $db->query(
                "INSERT INTO rank_tokens
									SET user_id = '$int_user_id',
										available_tokens = '$tokens'
									ON DUPLICATE KEY UPDATE
										available_tokens = '$tokens'"
            );
            if(!$result) {
                throw new Exception("Could not give prize $db_val to $name. ".$db->get_error());
            }
        }
        else if($db_val == 'crown_hat') {
            $parts = array();
            $parts[] = 6;
            award_parts($db, $int_user_id, 'hat', $parts);
        }
        else if($db_val == 'cowboy_hat') {
            $parts = array();
            $parts[] = 5;
            award_parts($db, $int_user_id, 'hat', $parts);
        }

        //send them a PM
        $message = "$name, congratulations on earning $min_score points for Team Jiggmin! You have been awarded with a $prize_str. \n\nThanks for helping us take over the world! (or cure cancer)\n\n- Jiggmin";
        $db->call('message_insert', array($int_user_id, 1, $message, '0'));
        
        //remember that this prize has been given
        $result = $db->query(
            "UPDATE folding_at_home
								SET $db_val = 1
								WHERE user_id = '$int_user_id'
								LIMIT 1"
        );
        if(!$result) {
            throw new Exception("Could not update $db_val status for $name.");
        }
    }
}



function replace_name($name) 
{
    global $name_switch_array;
    $name = str_replace('_', ' ', $name);
    if(isset($name_switch_array[strtolower($name)])) {
        $new_name = $name_switch_array[strtolower($name)];
        output("replacing $name with $new_name");
        $name = $new_name;
    }
    return $name;
}






//--- handy output function; never leave home without it! --------------------------------------------------
function output($str) 
{
    echo("* $str \n");
}

?>
