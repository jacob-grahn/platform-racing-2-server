<?php

function order_request_handler( $db, $request ) {
	
	//--- sort incoming data
	$event = $request->event; //item_order_request
	$game_id = $request->game_id; //The game_id.
	$buyer_id = $request->buyer_id; //The id of the user making the purchase.
	$recipient_id = $request->recipient_id; //The id of the user to receive the items.
	$order_id = $request->order_id; //A unique order id for this order in our database.
	$order_info = $request->order_info; //The order info string you passed into purchaseItemsRemote
	list($pr2_user_id, $item_slug) = explode(',', $order_info);

	//---
	$items_raw = describeVault( $db, $pr2_user_id, array($item_slug) );
	
	//---
	$items = array();
	foreach( $items_raw as $raw ) {
		$items[] = format_for_kong( $raw );
	}

	//---
	$reply = new stdClass();
	$reply->items = $items;
	return( $reply );
}



function format_for_kong( $desc ) {
	$item = new stdClass();
	$item->name = $desc->title;
	$item->description = $desc->description;
	$item->price = $desc->price;
	$item->image_url = $desc->imgUrlSmall;
	return( $item );
}

?>
