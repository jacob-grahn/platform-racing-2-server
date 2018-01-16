<?php

header("Content-type: text/plain");

require_once( '../../fns/all_fns.php' );
require_once( 'vault_descriptions.php' );

try {
	//--- connect
	$db = new DB();
	
	
	//--- create listings
	$user_id = token_login($db, true);
	$raw_listings = describeVault( $db, $user_id, array('stats-boost', 'epic-everything', 'guild-fred', 'guild-ghost', 'happy-hour', 'rank-rental', 'djinn-set', 'king-set', 'queen-set', 'server-1-day', 'server-30-days') );

	
	//--- weed out only the info we want to return
	$listings = array();
	foreach( $raw_listings as $raw ) {
		$listings[] = makeListing( $raw );
	}

	//--- reply		
	$r = new stdClass();
	$r->success = true;
	$r->listings = $listings;
	$r->title = 'Stayin\' Alive Sale';
	$r->sale = true;
	echo json_encode( $r );
}

catch(Exception $e) {
	$r = new stdClass();
	$r->state = 'canceled';
	$r->error = $e->getMessage();
	echo json_encode( $r );
}


function makeListing( $desc ) {
	$obj = new stdClass();
	$obj->slug = $desc->slug;
	$obj->title = $desc->title;
	$obj->imgUrl = $desc->imgUrl;
	$obj->price = $desc->price;
	$obj->description = $desc->description;
	$obj->longDescription = $desc->faq;
	$obj->available = $desc->available;
	if( isset($desc->discount) ) {
		$obj->discount = $desc->discount;
	}
	return $obj;
}



?>
