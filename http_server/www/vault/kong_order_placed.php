<?php

function order_placed_handler( $db, $request ) {
	$event = $request->event; //item_order_placed
	$game_id = $request->game_id; //The game_id.
	$buyer_id = $request->buyer_id; //The id of the user making the purchase.
	$recipient_id = $request->recipient_id; //The id of the user to receive the items.
	$order_id = $request->order_id; //A unique order id for this order in our database.
	$order_info = $request->order_info; //The order info string you passed into purchaseItemsRemote
	list($pr2_user_id, $slug) = explode(',', $order_info);

	//--- check that the item is available
	$descs = describeVault($db, $pr2_user_id, array($slug));
	$desc = $descs[0];
	if( $desc->available == false ) {
		throw new Exception( 'This item is no longer available' );
	}

	//--- apply item to player's account
	$user = $db->grab_row( 'user_select_expanded', array($pr2_user_id) );
	unlock_item( $db, $pr2_user_id, $user->guild, $user->server_id, $slug, $user->name, $recipient_id, $order_id, $desc->title );

	//--- tell it
	$reply = new stdClass();
	$reply->state = 'completed'; //$reply->state = 'canceled';
	return( $reply );
}



function unlock_item( $db, $user_id, $guild_id, $server_id, $slug, $user_name, $kong_user_id, $order_id, $title ) {
	error_log("unlock_item: $user_id, $guild_id, $server_id, $slug, $user_name, $kong_user_id, $order_id");
	$db->call( 'purchase_insert_2', array( $user_id, $guild_id, $slug, $kong_user_id, $order_id ), 'Could not record your purchase.' );
	$command = "unlock_perk`$slug`$user_id`$guild_id`$user_name";
	$reply = '';
	$target_servers = array();

	if( $slug == 'guild-fred' ) {
		$reply = "Fred smiles on you!";
	}

	else if( $slug == 'guild-ghost' ) {
		$reply = "Ninja mode: engage!";
	}

	else if( $slug == 'king-set' ) {
		award_part( $db, $user_id, 'head', 28 );
		award_part( $db, $user_id, 'body', 26 );
		award_part( $db, $user_id, 'feet', 24 );
		award_part( $db, $user_id, 'eHead', 28 );
		award_part( $db, $user_id, 'eBody', 26 );
		award_part( $db, $user_id, 'eFeet', 24 );
		$command = "unlock_set_king`$user_id";
		$reply = "The Wise King set has been added your account!";
	}

	else if( $slug == 'queen-set' ) {
		award_part( $db, $user_id, 'head', 29 );
		award_part( $db, $user_id, 'body', 27 );
		award_part( $db, $user_id, 'feet', 25 );
		award_part( $db, $user_id, 'eHead', 29 );
		award_part( $db, $user_id, 'eBody', 27 );
		award_part( $db, $user_id, 'eFeet', 25 );
		$command = "unlock_set_queen`$user_id";
		$reply = "The Wise Queen set has been added your account!";
	}

	else if( $slug == 'djinn-set' ) {
		award_part( $db, $user_id, 'head', 35 );
		award_part( $db, $user_id, 'body', 35 );
		award_part( $db, $user_id, 'feet', 35 );
		award_part( $db, $user_id, 'eHead', 35 );
		award_part( $db, $user_id, 'eBody', 35 );
		award_part( $db, $user_id, 'eFeet', 35 );
		$command = "unlock_set_djinn`$user_id";
		$reply = "The Frost Djinn set has been added your account!";
	}

	else if( $slug == 'epic-everything' ) {
	    award_part( $db, $user_id, 'eHat', '*' );
	    award_part( $db, $user_id, 'eHead', '*' );
	    award_part( $db, $user_id, 'eBody', '*' );
	    award_part( $db, $user_id, 'eFeet', '*' );
	    $command = "unlock_epic_everything`$user_id";
	    $reply = "All Epic Upgrades are yours!";
	}

	else if( $slug == 'happy-hour' ) {
		$target_servers = array( $server_id );
		$reply = "This is the happiest hour ever!";
	}

	else if( $slug == 'server-1-day' || $slug == 'server-30-days' ) {
		$command = '';
		$seconds = 0;
		if( $slug == 'server-1-day' ) {
			$seconds = 60*60*24;
		}
		if( $slug == 'server-30-days' ) {
			$seconds = 60*60*24*30;
		}

		$result = create_server( $db, $guild_id, $seconds );

		if( $result == 0 ) {
			throw new Exception( 'Could not start the server.' );
		}
		if( $result == 1 ) {
			$reply = 'The best server ever is starting up! ETA 2 minutes.';
		}
		if( $result == 2 ) {
			$reply = 'The life of your private server has been exteded! Long live your guild!';
		}
	}

	else if( $slug == 'rank-rental' ) {
		$db->call( 'rank_token_rental_insert', array($user_id, $guild_id) );

		$obj = new stdClass();
		$obj->user_id = $user_id;
		$obj->guild_id = $guild_id;
		$data = json_encode( $obj );

		$command = "unlock_rank_token_rental`$data";
		$reply = 'You just got a rank token!';
	}

	else {
		throw new Exception( "Item not found: " . strip_tags($slug, '<br>') );
	}

	if( $command != '' ) {
		poll_servers_2( $db, $command, false, $target_servers );
	}
	if( $reply != '' ) {
		$obj = new stdClass();
		$obj->user_id = $user_id;
		$obj->message = $reply;
		$data = json_encode( $obj );
		poll_servers_2( $db, "send_message_to_player`$data", false, array($server_id) );
	}

	send_confirmation_pm( $db, $user_id, $title, $order_id );

	return $reply;
}



function send_confirmation_pm( $db, $user_id, $title, $order_id ) {
	$pm = "Thank you for your support! This PM is to confirm your order.
item: $title
order id: $order_id";
	$db->call('message_insert', array($user_id, 1, $pm, '0'));
}



function create_server( $db, $guild_id, $seconds_of_life ) {
	global $COMM_PASS;
	$result = $db->call( 'server_select_by_guild_id', array($guild_id), 'Could not check if you already have a guild server.' );
	$guild = $db->grab_row( 'guild_select', array($guild_id), 'Could not select your guild.' );
	$port = 1 + $db->grab( 'port', 'servers_select_highest_port', array(), 'Could not select port.' );
	$server_name = $guild->guild_name;
	$address = 'assign';
	$expire_time = time() + $seconds_of_life;
	$salt = $COMM_PASS;
	$guild_id = $guild->guild_id;

	if( $result->num_rows <= 0 ) {
		$db->call( 'server_insert', array($server_name, $address, $port, $expire_time, $salt, $guild_id), 'Could not insert server.' );
		return( 1 );
	}
	else {
		$server = $result->fetch_object();
		$server_id = $server->server_id;
		$expire_time_2 = strtotime($server->expire_date) + $seconds_of_life;
		if( $expire_time_2 > $expire_time ) {
			$expire_time = $expire_time_2;
		}
		$db->call( 'server_update_expire_date', array($server_id, $expire_time, $server_name), "Could not update server. $server_id, $server_name, $expire_time" );
		return( 2 );
	}
}

?>
