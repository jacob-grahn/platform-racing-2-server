<?php

require_once('../fns/all_fns.php');

$level_id = find('level_id');
$new_rating = find('rating');

$level_id = addslashes($level_id);

$time = time();
$old_weight = 0;
$weight = 1;
$old_rating = 0;

$ip = get_ip();

$safe_ip = addslashes($ip);
$safe_new_rating = addslashes($new_rating);

try{
	//error check
	$new_rating = round($new_rating);
	if(is_nan($new_rating) || $new_rating < 1 || $new_rating > 5){
		throw new Exception('Could not vote $new_rating.');
	}

	//connect to the db
	$db = new DB();

	//check thier login
	$user_id = token_login($db);

	//see if they made this level
	$result = $db->query("select level_id
									from pr2_levels
									where user_id = '$user_id'
									and level_id = '$level_id'
									limit 0, 1");
	if(!$result){
		throw new Exception('Could not check your voting status.');
	}
	if($result->num_rows > 0){
		throw new Exception('You can\'t vote on yer own level, matey!');
	}

	//get their voting weight
	$rank_result = $db->query("select rank
											from pr2
											where user_id = '$user_id'
											limit 0, 1");
	if(!$rank_result){
		throw new Exception('Could not get your rank.');
	}
	if($rank_result->num_rows > 0){
		$rank_row = $rank_result->fetch_object();
		$weight = $rank_row->rank;
	}
	if($weight > 10) {
		$weight = 10;
	}
	if($weight < 1) {
		$weight = 1;
	}

	//see if they have voted on this level before
	$vote_result = $db->query("select rating, weight
										from pr2_ratings
										where user_id = '$user_id'
										and level_id = '$level_id'
										limit 0, 1");
	if(!$vote_result){
		throw new Exception('Could not check to see if you have voted on this course before');
	}

	if($vote_result->num_rows <= 0) {
		$vote_result = $db->query("select rating, weight
											from pr2_ratings
											where ip = '$safe_ip'
											and level_id = '$level_id'
											limit 0, 1");
		if(!$vote_result){
			throw new Exception('Could not check to see if you have ip voted on this course before');
		}
	}

	//if they have, they must wait
	if($vote_result->num_rows > 0){
		throw new Exception('You have already voted on this level. You can vote on it again in a week.');
	}

	//if they haven't add their vote
	else{
		$result = $db->query("insert into pr2_ratings
										set rating = '$safe_new_rating',
											user_id = '$user_id',
											level_id = '$level_id',
											weight = '$weight',
											time = '$time',
											ip = '$safe_ip'");
		if(!$result){
			throw new Exception('Could not add your vote');
		}
	}

	//get the average rating and votes so I can do some math
	$result = $db->query("select rating, votes
									from pr2_levels
									where level_id = '$level_id'
									limit 0, 1");
	if(!$result){
		throw new Exception('Could not retireve old rating.');
	}
	if($result->num_rows <= 0){
		throw new Exception('Course not found. This is probably because the level has been updated since you downloaded it.');
	}
	$row = $result->fetch_object();
	$average_rating = $row->rating;
	$votes = $row->votes;

	//do some math!
	$total_rating = $average_rating * $votes;
	$total_rating -= $weight * $old_rating;
	$total_rating += $weight * $new_rating;
	$votes += $weight - $old_weight;
	if($votes <= 0) {
		$new_average_rating = 0;
	}
	else {
		$new_average_rating = $total_rating / $votes;
	}

	if($new_average_rating > 5) {
		$new_average_rating = 0;
		$votes = 0;
	}

	//put the final average back into the level
	if(!is_nan($new_average_rating)){
		$result = $db->query("update pr2_levels
										set rating = '$new_average_rating',
											votes = '$votes'
										where level_id = '$level_id'
										limit 1");
		if(!$result){
			throw new Exception('Could not update rating.');
		}
	}

	//echo a message back
	echo 'message=';
	$old = round($average_rating, 2);
	$new = round($new_average_rating, 2);
	if($old == 0){
		$old = 'none';
	}
	if($old_rating == 0){
		echo 'Thank you for voting! Your vote of '.$new_rating.' changed the average rating from '
		.$old.' to '.$new.'.';
	}else{
		echo 'Thank you for voting! You changed your vote from '.$old_rating.' to '
		.$new_rating.', which changed the average rating from '.$old.' to '.$new.'.';
	}
}
catch(Exception $e){
	echo 'error='.$e->getMessage();
}


?>
