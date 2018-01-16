<?php

/*

require_once('../fns/all_fns.php');


//--- connect to the db ----------------------------------------------------------------------------------
$db = new DB( fah_connect(), false );
$pr2_db = new DB();


$contributors = retrieve_fah_contributors($db);
$contributors = retrieve_pr2_accounts($db, $pr2_db, $contributors);
$contributors = remove_inactive($contributors);
$contributors = remove_prev_winners($contributors);
$winner = $contributors[array_rand($contributors)];
send_message_to_contributors($pr2_db, $contributors, $winner);
output('winner: '.$winner->pr2->name);

function output($str) {
    echo "* $str \n";
}


function retrieve_fah_contributors($db) {
    $result = $db->call('gains_select_tallies');
    $arr = $db->to_array($result);
    return $arr;
}


function retrieve_pr2_accounts($db, $pr2_db, $contributors) {
    $arr = array();
    foreach($contributors as $contr) {
	$contr->pr2_name = retrieve_pr2_name($db, $contr->fah_name);
	$contr->pr2 = retrieve_pr2_account($pr2_db, $contr->pr2_name);
	if(isset($contr->pr2) && $contr->pr2 !== NULL) {
	    $contr->epic_hats = retrieve_epic_hats($pr2_db, $contr->pr2->user_id);
	    array_push($arr, $contr);
	}
    }
    return $arr;
}


function remove_inactive($contributors) {
    $arr = array();
    $min_time = time() - 60*60*24*7; //one week
    foreach($contributors as $contr) {
	if($contr->pr2->time > $min_time) {
	    array_push($arr, $contr);
	}
    }
    return $arr;
}


function remove_prev_winners($contributors) {
    $arr = array();
    foreach($contributors as $contr) {
	$epic_hats_arr = explode(',', $contr->epic_hats);
	if($contr->epic_hats === NULL || array_search(5, $epic_hats_arr) === false) {
	    array_push($arr, $contr);
	}
    }
    return $arr;
}


function retrieve_pr2_name($db, $fah_name) {
    $default_pr2_name = strtolower(str_replace('_', ' ', $fah_name));
    $pr2_name = $db->grab_row('pr2_name', 'pr2_name_link_select', array($fah_name), 'retrieve_pr2_name error', true);
    if($pr2_name === NULL) {
	$pr2_name = $default_pr2_name;
    }
    return $pr2_name;
}


function retrieve_pr2_account($pr2_db, $pr2_name) {
    $account = $pr2_db->grab_row('user_select_by_name', array($pr2_name), 'retrieve_pr2_account error', true);
    return $account;
}

function retrieve_epic_hats($pr2_db, $pr2_user_id) {
    $epic_hats = $pr2_db->grab('epic_hats', 'epic_upgrades_select', array($pr2_user_id), 'retrieve_epic_hats error', true);
    return $epic_hats;
}

function send_message_to_contributors($pr2_db, $contributors, $winner) {
    foreach($contributors as $contr) {
	//output("{$contr->pr2->name}, thank you for contributing to Team Jiggmin! In addition to practically curing cancer single handedly, you have been entered into the drawing for an Epic Cowboy Hat. \n\nThe winner this week is... \n\n{$winner->pr2->name}!");
	send_message($pr2_db, $contr->pr2->user_id, "{$contr->pr2->name}, thank you for contributing to Team Jiggmin! In addition to practically curing cancer single handedly, you have been entered into the drawing for an Epic Cowboy Hat. \n\nThe winner this week is... \n\n{$winner->pr2->name}!");
    }
}

function send_message($db, $user_id, $message){
	$time = time();
	$safe_user_id = $db->real_escape_string($user_id);
	$safe_message = $db->real_escape_string($message);
	$result = $db->query("insert into messages
									set from_user_id = '1',
										to_user_id = '$safe_user_id',
										message = '$safe_message',
										time = '$time'");
	if(!$result){
		throw new Exception("Could not send this message to $user_id: $message");
	}
}

*/

?>
