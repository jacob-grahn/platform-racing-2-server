<?php

header("Content-type: text/plain");

require_once(__DIR__ . '/kong_order_placed.php');
require_once(__DIR__ . '/kong_order_request.php');
require_once(__DIR__ . '/vault_descriptions.php');
require_once(__DIR__ . '/../../fns/all_fns.php');

try {

	//--- parse the incoming message
	$encrypted_request = $_POST['signed_request'];
	$request = parse_signed_request( $encrypted_request, $KONG_API_PASS );

	//--- connect
	$db = new DB();

	//---
	$event = $request->event;
	if( $event == 'item_order_request' ) {
		$reply = order_request_handler( $db, $request );
	}
	if( $event == 'item_order_placed' ) {
		$reply = order_placed_handler( $db, $request );
	}

	//---
	echo json_encode($reply);
}

catch(Exception $e) {
	$r = new stdClass();
	$r->error = $e->getMessage();
	error_log( 'caught error: ' . $r->error);
	echo json_encode( $r );
}




//---
function parse_signed_request($signed_request, $secret) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2);

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload));

  if (strtoupper($data->algorithm) !== 'HMAC-SHA256') {
    throw new Exception('Unknown algorithm. Expected HMAC-SHA256');
  }

  // check sig
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    throw new Exception('Bad Signed JSON signature!');
  }

  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}

?>
