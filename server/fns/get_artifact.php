#!/usr/bin/php
<?php

require_once(__DIR__ . '/all_fns.php');
require_once(__DIR__ . '/query_fns.php');

function artifact_first_check($port, $player) {

	$caught_exception = false;
	$user_id = $player->user_id;
	$user_name = $player->name;

	try {

		$db = new DB();
		$first_finder = $db->grab( 'first_finder', 'artifact_find', array($user_id) );

		if( $first_finder == $user_id ) {
			award_part( $db, $user_id, 'head', 27 );
			award_part( $db, $user_id, 'body', 21 );
			award_part( $db, $user_id, 'feet', 28 );
		}

	}

	catch(Exception $e) {
		$caught_exception = true;
		$message = $e->getMessage();
		echo "Error: ".$message;
		return false;
	}

	if(!$caught_exception) {
		echo "Awarded bubble set to $user_name for finding the artifact first.";
		$player->write('message`The most humble of congratulations to you for finding the artifact first! To commemorate this momentous occasion, you\'ve been awarded with your very own bubble set.\n\nThanks for playing Platform Racing 2!\n- Jiggmin');
		return true;
	}

}

?>
