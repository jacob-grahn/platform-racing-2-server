<?php

require_once( '../fns/all_fns.php' );
require_once( '../fns/classes/S3.php' );

try {
	
	$image = file_get_contents("php://input");
	$image_rendered = imagecreatefromstring($image);
	
	
	//--- connect to the db
	$db = new DB();
	$s3 = s3_connect();
	
	
	//--- check thier login
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array($user_id) );
	
	
	//--- sanity check
	if( $account->rank < 20 ) {
		throw new Exception( 'Must be rank 20 or above to upload an emblem.' );
	}
	if( $account->power <= 0 ) {
		throw new Exception( 'Guests can not upoad emblems.' );
	}
	if( !isset( $image ) ) {
		throw new Exception( 'No image recieved.' );
	}
	if( strlen( $image ) > 20000 ) {
		throw new Exception( 'Image is too large. ' . strlen( $image ) );
	}
	if( getimagesize( $image_rendered ) === false) {
	    throw new Exception( 'File is not an image' );
	}
	
	
	//--- send the image to s3
	$filename = $user_id . '-' . time() . '.jpg';
	$bucket = 'pr2emblems';
	$result = $s3->putObject( $image, $bucket, $filename );
	if(!$result) {
		throw new Exception( 'Could not save image. :(' );
	}
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->len = strlen( $image );
	$reply->filename = $filename;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
