<?php

header("Content-type: text/plain");

require_once( '../../fns/all_fns.php' );

$img_id = find('img');

$ip = get_ip();

try {

	if(!is_numeric($img_id) || $img_id < 0 || $img_id > 1) {
		throw new Exception('Invalid img');
	}

  //--- rate limit
	// rate_limit('cat-img-'.$ip, 60*5, 20);

	//--- get your captcha in progress
	$key = 'cat-' . $ip;
	$success = false;
	$str_val = apc_fetch($key, $success);
	if( !$success ) {
		throw new Exception('Could not find a pending captcha for your ip.');
	}

	$val = json_decode($str_val);
	$img_dir = $val->imgs[$img_id];

	//---
	if(!file_exists($img_dir)) {
		throw new Exception('Image does not exist. '.$img_dir);
	}

	//---
	$im = new Imagick($img_dir);

	//--- random slight rotation
	$rot = rand(-70000, 70000) / 50000;
	$im->rotateImage(new ImagickPixel('#00000000'), $rot);

	//--- zoom in a bit
	$geo = $im->getImageGeometry();
	$sizex = $geo['width'];
	$sizey = $geo['height'];
	$zoom = rand(4, 10) / 100;
	$cx = $sizex * $zoom;
	$cy = $sizey * $zoom;
	$x = round($cx / 2);
	$y = round($cy / 2);
	$width = round($sizex - $cx);
	$height = round($sizey - $cy);
	$im->cropImage($width, $height, $x, $y);

	//--- resize
	$max_width = 200;
	$max_height = 200;
	//$geo = $im->getImageGeometry();
   // if ($geo['width'] > $max_width || $geo['height'] > $max_height) {
		 $im->resizeImage($max_width, $max_height, imagick::FILTER_LANCZOS, 0.9, true);
	//}

	//--- maybe flip
	if( rand(0,1) == 0 ) {
		$im->flopimage();
	}

	//--- show it to the world
	echo $im;
}

catch(Exception $e) {
	echo $e->getMessage();
	/*$error_img = file_get_contents('error.jpg');
	echo $error_img;*/
}

?>
