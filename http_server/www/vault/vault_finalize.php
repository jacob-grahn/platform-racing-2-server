<?php

header("Content-type: text/plain");

require_once('../../fns/all_fns.php');

$game_auth_token = find( 'game_auth_token' );
$kong_user_id = find( 'kong_user_id' );
$server_id = find( 'server_id' );
$item_id = -1;
$api_key = $KONG_API_PASS;

try {

	//--- sanity check
	if( !isset($game_auth_token) ) {
		throw new Exception( 'Invalid game_auth_token' );
	}
	if( !isset($kong_user_id) ) {
		throw new Exception( 'Invalid kong_user_id' );
	}
	if( !isset($server_id) ) {
		throw new Exception( 'Invalid server_id' );
	}


	//--- connect
	$db = new DB();


	//--- gather infos
	$user_id = token_login($db);
	$user = $db->grab_row( 'user_select', array( $user_id ), 'Could not fetch your info.' );

	if( $user->power <= 0 ) {
		throw new Exception( 'Guests can not buy things...' );
	}


	//--- get the list of items they own
	$items = get_owned_items( $api_key, $kong_user_id );


	//--- loop through and assign any items to their account
	$results = array();
	foreach( $items as $item ) {
		$item_id = $item->id;
		$slug = $item->identifier;
		$remaining_uses = $item->remaining_uses;
		if( $remaining_uses >= 1 ) {
			$reply = unlock_item( $db, $user_id, $user->guild, $server_id, $slug, $user->name, $kong_user_id );
			$results[] = use_item( $api_key, $game_auth_token, $kong_user_id, $item_id );
		}
	}

	//--- reply
	$r = new stdClass();
	$r->results = $results;
	if( isset($reply) ) {
		$r->message = $reply;
	}
	echo json_encode( $r );
}

catch(Exception $e) {
	$r = new stdClass();
	$r->error = $e->getMessage();
	echo json_encode( $r );
}




function get_owned_items( $api_key, $kong_user_id ) {
	$url = 'http://www.kongregate.com/api/user_items.json';
	$get = array( 'api_key'=>$api_key, 'user_id'=>$kong_user_id );
	$item_str = curl_get( $url, $get );
	$item_result = json_decode( $item_str );

	if( !$item_result->success ) {
		throw new Exception( 'Could not retrieve a list of your purchased items.' );
	}

	return $item_result->items;
}





function use_item( $api_key, $game_auth_token, $kong_user_id, $item_id ) {
	$url = 'http://www.kongregate.com/api/use_item.json';
	$post = array( 'api_key'=>$api_key, 'game_auth_token'=>$game_auth_token, 'user_id'=>$kong_user_id, 'id'=>$item_id );
	$use_result_str = curl_post( $url, $post );
	$use_result = json_decode( $use_result_str );

	if( !$use_result->success ) {
		throw new Exception( 'Could not use the item.' );
	}

	return $use_result;
}



/**
 * Send a POST requst using cURL
 * @param string $url to request
 * @param array $post values to send
 * @param array $options for cURL
 * @return string
 */
function curl_post($url, array $post = NULL, array $options = array())
{
    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}


/**
 * Send a GET requst using cURL
 * @param string $url to request
 * @param array $get values to send
 * @param array $options for cURL
 * @return string
 */
function curl_get($url, array $get = NULL, array $options = array())
{
    $defaults = array(
        CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}



?>
