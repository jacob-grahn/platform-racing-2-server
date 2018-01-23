<?php


function describeVault( $db, $user_id, $arr ) {

	//--- gather infos
	$user = $db->grab_row( 'user_select_expanded', array($user_id) );
	$server = $db->grab_row( 'server_select', array($user->server_id), 'You don\'t seem to be logged into a pr2 server.' );
	if( $user->guild != 0 ) {
		$guild = $db->grab_row( 'guild_select', array($user->guild) );
	}
	else {
		$guild = false;
	}

	//--- collect requested descriptions
	$descriptions = array();
	foreach( $arr as $slug ) {
		if( $slug == 'stats-boost' ) {
			$item = describeSuperBooster( $user, $server );
		}
		else if( $slug == 'guild-fred' ) {
			$item = describeFred();
		}
		else if( $slug == 'guild-ghost' ) {
			$item = describeGhost();
		}
		else if( $slug == 'happy-hour' ) {
			$item = describeHappyHour( $server );
		}
		else if( $slug == 'rank-rental' ) {
			$item = describeRankRental( $db, $user );
		}
		else if( $slug == 'king-set' ) {
			$item = describeKing( $user );
		}
		else if( $slug == 'queen-set' ) {
			$item = describeQueen( $user );
		}
		else if( $slug == 'djinn-set' ) {
			$item = describeDjinn( $user );
		}
		else if( $slug == 'server-1-day' ) {
			$item = describePrivateServer1( $user, $guild );
		}
		else if( $slug == 'server-30-days' ) {
			$item = describePrivateServer30( $user, $guild );
		}
		else if( $slug == 'epic-everything' ) {
			$item = describeEpicEverything( $user );
		}
		else {
			throw new Exception( 'Unknown item type.' );
		}

		if( $item->price != 0 ) {
			if( $item->slug === 'epic-everything' ) {
			    $item->price = floor( $item->price * 0.75 );
			    $item->discount = '25% off';
			}
			else {
			    $item->price = floor( $item->price * 0.25 );
			    $item->discount = '75% off';
			}
		}

		$descriptions[] = $item;
	}

	//---
	return( $descriptions );
}


function describeSuperBooster( $user, $server ) {
	$d = new stdClass();
	$d->slug = 'stats-boost';
	$d->title = 'Super Booster';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Super-Booster-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Super-Booster-112x63.png';
	$d->price = 0;
	$d->description = 'Boost all of your stats by 10 for one race. One use per day.';
	$d->available = false;
	$d->faq =
		"Can I use more than one Super Booster per day if I pay for it?\n"
		."Nope!\n\n";

	if( $server->tournament == 0 ) {
		$d->available = !apcu_fetch( "sb-$user->user_id" );
	}

	return( $d );
}


function describeFred() {
	$d = new stdClass();
	$d->slug = 'guild-fred';
	$d->title = 'Guild de Fred';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Guild-de-Fred-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Guild-de-Fred-40x40.png';
	$d->price = 20;
	$d->description = 'You and your guild get to party as Fred for an hour.';
	$d->available = true;
	$d->faq =
		"Is the Guild de Fred power-up useful?\n"
		."- Not at all!\n\n"
		."Do I get to run around as a giant cactus?\n"
		."- Yes. Yes you do.\n\n"
		."How does Guild de Fred work?\n"
		."- A Giant Cactus body is temporarily added to your account. You can switch between the Giant Cactus body and your other bodies normally.\n\n";

	return( $d );
}


function describeGhost() {
	$d = new stdClass();
	$d->slug = 'guild-ghost';
	$d->title = 'Guild de Ghost';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Guild-de-Ghost-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Guild-de-Ghost-40x40.png';
	$d->price = 10;
	$d->description = 'You and your guild gain (very) invisible parts for an hour.';
	$d->available = true;
	$d->faq =
		"Will this make me feel like a ninja?\n"
		."You'll be so ninja.\n\n"
		."Is the Guild de Ghost power-up useful?\n"
		."- It may actually be a massive disadvantage!\n\n"
		."How does Guild de Ghost work?\n"
		."- A very invisible head, body, and feet are temporarily added to your account. You can switch between these parts and your other parts normally.\n\n";

	return( $d );
}


function describeHappyHour( $server ) {
	$d = new stdClass();
	$d->slug = 'happy-hour';
	$d->title = 'Happy Hour';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Happy-Hour-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Happy-Hour-40x40.png';
	$d->price = 50;
	$d->description = 'Is there a happy hour right now? Well there should be.';
	$d->available = false;
	$d->faq =
		"What's a Happy Hour?\n"
		."- During a Happy Hour everyone on this server will receive double experience points, and everyone's speed, acceleration, and jumping are increased to 100.\n\n"
		."Can a Happy Hour be used on a private server?\n"
		."- Yup!\n\n";

	if( $server->tournament == 0 ) {
		$d->available = true;
	}

	return( $d );
}


function describeRankRental( $db, $user ) {
	$rented_tokens = $db->grab( 'count', 'rank_token_rentals_count', array( $user->user_id, $user->guild) );
	$d = new stdClass();
	$d->slug = 'rank-rental';
	$d->title = 'Rank Token++';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Rank-Token-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Rank-Token-40x40.png';
	$d->price = 50 + (20*$rented_tokens);
	$d->description = 'You and your guild all gain a rank token for a week.';
	$d->available = true;
	$d->faq =
		"What's a Rank Token?\n"
		."- You can use rank tokens to increase or decrease your rank at will. A rank 40 account with 3 rank tokens could become a rank 43 account, for example.\n\n"
		."Why does the price change?\n"
		."- The price of a new Rank Token++ depends on how many you currently have.\n"
		."  1st: 50 kreds\n"
		."  2nd: 70 kreds\n"
		."  3rd: 90 kreds\n"
		."  etc\n\n"
		."How many tokens can be used at once?\n"
		."- Up to 21 rank tokens can be rented at a time.\n\n";

	return( $d );
}


function describeKing( $user ) {
	$d = new stdClass();
	$d->slug = 'king-set';
	$d->title = 'Wise King';
	$d->imgUrl = 'https://pr2hub.com/img/vault/King-Set-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/King-Set-40x40.png';
	$d->price = 30;
	$d->description = 'Permanently add the Wise King Set to your account.';
	$d->available = false;
	$d->faq =
		"Does the Wise King set give me any stat boosts?\n"
		."- Nope!\n\n"
		."Does the Wise King set make me look totally rad?\n"
		."- Totally.\n\n";

	$heads = explode( ',', $user->head_array );
	if( array_search(28, $heads) === false ) {
		$d->available = true;
	}

	return( $d );
}


function describeQueen( $user ) {
	$d = new stdClass();
	$d->slug = 'queen-set';
	$d->title = 'Wise Queen';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Queen-Set-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Queen-Set-40x40.png';
	$d->price = 30;
	$d->description = 'Permanently add the Wise Queen Set to your account.';
	$d->available = false;
	$d->faq =
		"Does the Wise Queen set give me any stat boosts?\n"
		."- Nope!\n\n"
		."Does the Wise Queen set make me look totally rad?\n"
		."- Totally.\n\n";

	$heads = explode( ',', $user->head_array );
	if( array_search(29, $heads) === false ) {
		$d->available = true;
	}

	return( $d );
}


function describeDjinn( $user ) {
	$d = new stdClass();
	$d->slug = 'djinn-set';
	$d->title = 'Frost Djinn';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Djinn-Set-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Djinn-Set-40x40.png';
	$d->price = 50;
	$d->description = 'Permanently add the Frost Djinn Set to your account.';
	$d->available = false;
	$d->faq =
		"Does the Frost Djinn set give me any stat boosts?\n"
		."- Nope!\n\n"
		."Does the Frost Djinn set make me look totally rad?\n"
		."- Totally.\n\n";

	$heads = explode( ',', $user->head_array );
	if( array_search(35, $heads) === false ) {
		$d->available = true;
	}

	return( $d );
}


function describePrivateServer1( $user, $guild ) {
	$d = new stdClass();
	$d->slug = 'server-1-day';
	$d->title = 'Private Server 1';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Private-Server-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Private-Server-40x40.png';
	$d->price = 20;
	$d->description = 'Create an exclusive server for your guild. Runs for 1 day.';
	$d->available = false;
	$d->faq =
		"Who can use a private server?\n"
		."- You and members of your guild can use your private server.\n\n"
		."Can moderators enter our private server?\n"
		."- Nope. You are the law. You'll even have a few mod powers.\n\n"
		."Can I make my own campaign?\n"
		."- Not currently.\n\n"
		."Why can't I create a private server?\n"
		."- This option is for guild owners only!\n\n";


	if( $guild && $guild->owner_id == $user->user_id ) {
		$d->available = true;
	}

	return( $d );
}


function describePrivateServer30( $user, $guild ) {
	$d = new stdClass();
	$d->slug = 'server-30-days';
	$d->title = 'Private Server 30';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Private-Server-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Private-Server-40x40.png';
	$d->price = 300;
	$d->description = 'Create an exclusive server for your guild. Runs for 30 days.';
	$d->available = false;
	$d->faq =
		"Who can use a private server?\n"
		."- You and members of your guild can use your private server.\n\n"
		."Can moderators enter our private server?\n"
		."- Nope. You are the law. You'll even have a few mod powers.\n\n"
		."Can I make my own campaign?\n"
		."- Not currently.\n\n"
		."Why can't I create a private server?\n"
		."- This option is for guild owners only!\n\n";

	if( $guild && $guild->owner_id == $user->user_id ) {
		$d->available = true;
	}

	return( $d );
}


function describeEpicEverything( $user, $hats ) {
	$d = new stdClass();
	$d->slug = 'epic-everything';
	$d->title = 'Epic Everything';
	$d->imgUrl = 'https://pr2hub.com/img/vault/Guild-de-Ghost-112x63.png';
	$d->imgUrlSmall = 'https://pr2hub.com/img/vault/Guild-de-Ghost-40x40.png';
	$d->price = 110;
	$d->description = 'Unlock all Epic Upgrades';
	$d->available = false;
	$d->faq =
		"What is an Epic Upgrade?\n"
		."- It gives you a second editable color on a part you already own!\n\n"
		."Does this include every Epic Upgrade that exists or ever will exist?\n"
		."- Sure does!\n\n"
		."Does this unlock all the parts too?\n"
		."- No, but all parts you win in the future will automatically come with an Epic Upgrade.\n\n"
		."Do epic upgrades provide a stat boost?\n"
		."- Nope!\n\n";

	$heads = explode( ',', $user->epic_heads );
	if( array_search('*', $heads) === false ) {
		$d->available = true;
	}

	return( $d );
}

?>
