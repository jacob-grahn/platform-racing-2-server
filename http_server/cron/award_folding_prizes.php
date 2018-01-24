<?php

require_once('../fns/all_fns.php');


$prize_array = array();
$processed_names = array();
$name_switch_array = array();


//--- connect to the db ----------------------------------------------------------------------------------
$fah_db = new DB( fah_connect(), false );
$pr2_db = new DB();



//--- create a list of existing users and their prizes --------------------------------------------------
$result = $pr2_db->query('select folding_at_home.*, users.name, users.status
							 	from folding_at_home, users
								where folding_at_home.user_id = users.user_id');

while($row = $result->fetch_object()) {
	$prize_array[strtolower($row->name)] = $row;
}



//--- create a list of name switches --------------------------------------------------------------------
$result = $fah_db->call( 'pr2_name_links_select' );
while( $row = $result->fetch_object() ) {
	$name_switch_array[ strtolower($row->fah_name) ] = strtolower( $row->pr2_name );
}



//--- get fah user stats -------------------------------------------------------------------------------
$result = $fah_db->call( 'stats_select_all' );
while( $user = $result->fetch_object() ) {
	add_prizes( $pr2_db, $user->fah_name, $user->points, $prize_array, $processed_names );
}



function add_prizes( $db, $name, $score, $prize_array, $processed_names ){
	$name = replace_name($name);
	$lower_name = strtolower( $name );

	if(!isset($processed_names[$lower_name])) {
		$processed_names[$lower_name] = 1;

		try{

			if(isset($prize_array[$lower_name])) {
				$row = $prize_array[$lower_name];
				$user_id = $row->user_id;
				$status = $row->status;
			}

			else {
				//grab their user id
				$safe_name = $db->real_escape_string($name);
				$result = $db->query("select user_id, status
												from users
												where name = '$safe_name'
												limit 0, 1");
				if(!$result){
					throw new Exception("Could not retrieve $name's user id.");
				}
				if($result->num_rows <= 0){
					throw new Exception("$name is not a registered user.");
				}
				$row = $result->fetch_object();
				$user_id = $row->user_id;
				$status = $row->status;


				//grab their F@H record
				$safe_user_id = $db->real_escape_string($user_id);
				$result = $db->query("select *
												from folding_at_home
												where user_id = '$safe_user_id'
												limit 0, 1");
				if(!$result){
					throw new Exception("Could not retrieve $name's F@H record.");
				}
				if($result->num_rows <= 0){
					//create a new F@H record for them if they don't have one
					$add_result = $db->query("insert into folding_at_home
														set user_id = '$safe_user_id'");
					if(!$add_result){
						throw new Exception("Could not create $name's F@H record.");
					}
					send_message($db, $user_id, "Welcome to Team Jiggmin, $name! Your help in taking over the world (or curing cancer) is much appreciated! \n\n- Jiggmin");
					throw new Exception("Successfully created $name's F@H record.");
				}
				$row = $result->fetch_object();
			}

			if($status != 'offline'){
				throw new Exception("We'll do this later, because $name is not offline. They are '$status'.");
			}

			//3 rank in pr2
			award_prize($db, $user_id, $name, $score, $row, 'r1', 1, '+1 rank token in Platform Racing 2');
			award_prize($db, $user_id, $name, $score, $row, 'r2', 500, '+1 rank token in Platform Racing 2');
			award_prize($db, $user_id, $name, $score, $row, 'r3', 1000, '+1 rank token in Platform Racing 2');

			//crown hat
			award_prize($db, $user_id, $name, $score, $row, 'crown_hat', 5000, 'Crown Hat in Platform Racing 2');

			//cowboy hat
			award_prize($db, $user_id, $name, $score, $row, 'cowboy_hat', 100000, 'Super Flying Cowboy Hat in PlatformRacing 2');

			//some more rank tokens
			award_prize($db, $user_id, $name, $score, $row, 'r4', 1000000, '+1 rank increase in Platform Racing 2');
			award_prize($db, $user_id, $name, $score, $row, 'r5', 10000000, '+1 rank increase in Platform Racing 2');

		}
		catch(Exception $e){
			//output( $e->getMessage() );
		}
	}
}



function award_prize($db, $user_id, $name, $score, $row, $db_val, $min_score, $prize_str){
	if($score >= $min_score && $row->{$db_val} != 1){

		output( "awarding $db_val to $name" );
		$row->{$db_val} = 1;

		//give the prize
		$safe_user_id = $db->real_escape_string($user_id);
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
			$result = $db->query("INSERT INTO rank_tokens
									SET user_id = '$safe_user_id',
										available_tokens = '$tokens'
									ON DUPLICATE KEY UPDATE
										available_tokens = '$tokens'");
			if(!$result){
				throw new Exception("Could not give prize $db_val to $name. ".$db->get_error());
			}
		}
		else if($db_val == 'crown_hat') {
			$parts = array();
			$parts[] = 6;
			award_parts($db, $user_id, 'hat', $parts);
		}
		else if($db_val == 'cowboy_hat') {
			$parts = array();
			$parts[] = 5;
			award_parts($db, $user_id, 'hat', $parts);
		}

		//send them a PM
		send_message($db, $user_id, "$name, congratulations on earning $min_score points for Team Jiggmin! You have been awarded with a $prize_str. \n\nThanks for helping us take over the world! (or cure cancer)\n\n- Jiggmin");

		//remember that this prize has been given
		$result = $db->query("update folding_at_home
										set $db_val = 1
										where user_id = '$safe_user_id'
										limit 1");
		if(!$result){
			throw new Exception("Could not update $db_val status for $name.");
		}
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



function replace_name($name){
	global $name_switch_array;
	$name = str_replace('_', ' ', $name);
	if( isset( $name_switch_array[strtolower($name)] ) ) {
		$new_name = $name_switch_array[strtolower($name)];
		output( "replacing $name with $new_name" );
		$name = $new_name;
	}
	return $name;
}






//--- handy output function; never leave home without it! --------------------------------------------------
function output( $str ) {
	echo( "* $str \n" );
}

?>
