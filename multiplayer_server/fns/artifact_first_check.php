<?php

require_once(__DIR__ . '/all_fns.php');

function artifact_first_check($port, $player) {
	global $db;

	$user_id = $player->user_id;
	$safe_user_name = htmlspecialchars($player->name);

	try {
		
		$first_finder = $db->grab( 'first_finder', 'artifact_find', array($user_id) );

		if( $first_finder == $user_id ) {
			
			/* What are we gonna tell the player when they win?
			How about display a prize window with the bubble head and the name "Bubble Set" */
			
			// make a prize array for the game to show the user
			$artifact_first_prize_popup = json_encode(array(
				"type" => "eHead",
				"id" => 27,
				"name" => "Bubble Set",
				"desc" => "For finding the artifact first, you earned your very own bubble set!",
				"universal" => true
				));
			
			// give all parts of the bubble set using the gain_part function from Player.php
			$player->gain_part( "head", 27, true );
			$player->gain_part( "body", 21, true );
			$player->gain_part( "feet", 28, true );
			
			// tell the world
			echo "Awarded bubble set to $safe_user_name for finding the artifact first.";
			$player->write( 'winPrize`' . $artifact_first_prize_popup );
			
			// pm the user (finishing touch!)
			$artifact_first_pm = 'Dear '.$safe_user_name.',

I\'d like to sincerely congratulate you for finding the artifact first! To commemorate this momentous occasion, you\'ve been awarded with your very own bubble set.

Thanks for playing Platform Racing 2!

- Jiggmin';
			
			$db->call( 'message_insert', array($user_id, 1, $artifact_first_pm, '0') );			
		}
	}

	catch(Exception $e) {
		$message = $e->getMessage();
		echo "Error: ".$message;
		return false;
	}

}

?>
