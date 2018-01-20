<?php

header("Content-type: text/plain;charset=UTF-8");

require_once('../fns/all_fns.php');

$target_name = find('name');

$friend = 0;
$ignored = 0;

try {

	$db = new DB();

	//--- check their login
	try {
		$user_id = token_login($db);
	}
	catch(Exception $e) {
		$friend = 0;
		$ignored = 0;
	}

	//--- get the target id
	$target_id = name_to_id($db, $target_name);

	//--- get dem infos
	$target = $db->grab_row( 'user_select_expanded', array( $target_id ) );

	if( $target->guild != 0 ) {
		try {
			$guild = $db->grab_row( 'guild_select', array( $target->guild ) );
			$guild_name = htmlspecialchars($guild->guild_name);
		}
		catch (Exception $e) {
			$guild_name = '';
		}
	}
	else {
		$guild_name = '';
	}

	if(!isset($target->used_tokens)) {
		$target->used_tokens = 0;
	}
	$login_date = date('j/M/Y', $target->time);
	$register_date = date('j/M/Y', $target->register_time);
	$active_rank = $target->rank + $target->used_tokens;
	$hats = count(explode(',', $target->hat_array))-1;

	if(isset($user_id)){
		$friend = $db->grab( 'is_friend', 'friend_check', array( $user_id, $target_id ) );
		$ignored = $db->grab( 'is_ignored', 'ignored_check', array( $user_id, $target_id ) );
	}


	//--- reply
	$r = new stdClass();
	$r->rank = $active_rank;
	$r->hats = $hats;
	$r->group = $target->power;
	$r->friend = $friend;
	$r->ignored = $ignored;
	$r->status = $target->status;
	$r->loginDate = $login_date;
	$r->registerDate = $register_date;
	$r->hat = $target->hat;
	$r->head = $target->head;
	$r->body = $target->body;
	$r->feet = $target->feet;
	$r->hatColor = $target->hat_color;
	$r->headColor = $target->head_color;
	$r->bodyColor = $target->body_color;
	$r->feetColor = $target->feet_color;
	$r->guildId = $target->guild;
	$r->guildName = $guild_name;
	$r->name = $target->name;
	$r->userId = $target->user_id;

	//--- epic upgrades
	if( !isset($target->epic_hats) ) {
		$r->hatColor2 = -1;
		$r->headColor2 = -1;
		$r->bodyColor2 = -1;
		$r->feetColor2 = -1;
	}
	else {
		$r->hatColor2 = test_epic( $target->hat_color_2, $target->epic_hats, $target->hat );
		$r->headColor2 = test_epic( $target->head_color_2, $target->epic_heads, $target->head );
		$r->bodyColor2 = test_epic( $target->body_color_2, $target->epic_bodies, $target->body );
		$r->feetColor2 = test_epic( $target->feet_color_2, $target->epic_feet, $target->feet );
	}

}

catch(Exception $e) {
	$r = new stdClass();
	$r->error = $e->getMessage();
}

echo json_encode( $r );


function test_epic( $color, $arr_str, $part ) {
	$ret = -1;
	if( isset($arr_str) && strlen($arr_str) > 0 ) {
		$arr = explode( ',', $arr_str );
		if( array_search( $part, $arr ) !== false || array_search('*', $arr) !== false  ) {
			$ret = $color;
		}
	}
	return $ret;
}


?>
