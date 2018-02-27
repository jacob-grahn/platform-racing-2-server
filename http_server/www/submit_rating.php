<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$level_id = $_POST['level_id'];
$new_rating = $_POST['rating'];

$level_id = mysqli_real_escape_string($level_id);

$time = time();
$old_weight = 0;
$weight = 1;
$old_rating = 0;

$ip = get_ip();

$safe_ip = mysqli_real_escape_string($ip);
$safe_new_rating = mysqli_real_escape_string($new_rating);

try {
	
	// POST check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception('Invalid request method.');
	}
	
	// rate limiting
	rate_limit('submit-rating-'.$ip, 5, 1);
	rate_limit('submit-rating-'.$ip, 30, 5);
	
	// sanity check: is the rating valid?
	$new_rating = round($new_rating);
	if(is_nan($new_rating) || $new_rating < 1 || $new_rating > 5){
		throw new Exception("Could not vote $new_rating.");
	}

	// connect
	$db = new DB();

	// check their login
	$user_id = token_login($db, false);
	
	// rate limiting
	rate_limit('submit-rating-'.$ip, 5, 1);
	rate_limit('submit-rating-'.$ip, 30, 5);

	// see if they made this level
	$result = $db->query("SELECT level_id
									FROM pr2_levels
									WHERE user_id = '$user_id'
									AND level_id = '$level_id'
									LIMIT 0, 1");
	if(!$result){
		throw new Exception('Could not check your voting status.');
	}
	if($result->num_rows > 0){
		throw new Exception("You can't vote on yer own level, matey!");
	}

	// get their voting weight
	$rank_result = $db->query("SELECT rank
									FROM pr2
									WHERE user_id = '$user_id'
									LIMIT 0, 1");
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

	// see if they have voted on this level before
	$vote_result = $db->query("SELECT rating, weight
									FROM pr2_ratings
									WHERE user_id = '$user_id'
									AND level_id = '$level_id'
									LIMIT 0, 1");
	if(!$vote_result){
		throw new Exception('Could not check to see if you have voted on this course before');
	}

	if($vote_result->num_rows <= 0) {
		$vote_result = $db->query("SELECT rating, weight
										FROM pr2_ratings
										WHERE ip = '$safe_ip'
										AND level_id = '$level_id'
										LIMIT 0, 1");
		if(!$vote_result){
			throw new Exception('Could not check to see if you have ip voted on this course before');
		}
	}

	// if they have, they must wait
	if($vote_result->num_rows > 0){
		throw new Exception('You have already voted on this level. You can vote on it again in a week.');
	}

	// if they haven't voted
	else{
		$result = $db->query("INSERT into pr2_ratings
										SET rating = '$safe_new_rating',
											user_id = '$user_id',
											level_id = '$level_id',
											weight = '$weight',
											time = '$time',
											ip = '$safe_ip'");
		if(!$result){
			throw new Exception('Could not add your vote');
		}
	}

	// get the average rating and votes so I can do some math
	$result = $db->query("SELECT rating, votes
									FROM pr2_levels
									WHERE level_id = '$level_id'
									LIMIT 0, 1");
	if(!$result){
		throw new Exception('Could not retrieve old rating.');
	}
	if($result->num_rows <= 0){
		throw new Exception('Course not found. This is probably because the level has been modified since you started playing it.');
	}
	$row = $result->fetch_object();
	$average_rating = $row->rating;
	$votes = $row->votes;

	// quick maths
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

	// put the final average back into the level
	if(!is_nan($new_average_rating)){
		$result = $db->query("UPDATE pr2_levels
										SET rating = '$new_average_rating',
											votes = '$votes'
										WHERE level_id = '$level_id'
										LIMIT 1");
		if(!$result){
			throw new Exception('Could not update rating.');
		}
	}

	// echo a message back
	echo 'message=Thank you for voting! ';
	$old = round($average_rating, 2);
	$new = round($new_average_rating, 2);
	if($old == 0){
		$old = 'none';
	}
	if($old_rating == 0){
		echo "Your vote of $new_rating changed the average rating from $old to $new.";
	}
	else{
		echo "You changed your vote from $old_rating to $new_rating, which changed the average rating from $old to $new.";
	}
}
catch (Exception $e) {
	$error = $e->getMessage();
	echo "error=$error";
}


?>
