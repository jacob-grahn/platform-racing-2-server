<?php

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();


//--- collect data
$artifact = $db->grab_row('artifact_location_select');
$level_id = $artifact->level_id;
$updated_time = strtotime( $artifact->updated_time );
$first_finder = $artifact->first_finder;

$level = $db->grab_row( 'level_select', array($level_id) );
$title = $level->title;
$user_id = $level->user_id;

$user = $db->grab_row(  'user_select', array($user_id) );
$user_name = $user->name;

if( $first_finder != 0 ) {
	$finder = $db->grab_row( 'user_select', array( $first_finder ) );
	$finder_name = $finder->name;
} else {
	$finder_name = '';
}


//--- form the base string we'll be creating
$str = "$title by $user_name";
$len = strlen( $str );


//--- figure out how much of the string to reveal
$elapsed = time() - $updated_time;
$perc = $elapsed / (60*60*24*3);
if( $perc > 1 ) {
	$perc = 1;
}
$hide_perc = 1 - $perc;
$hide_characters = round( $len * $hide_perc );
output( "hide_perc: $hide_perc" );
output( "hide_characters: $hide_characters" );
output( "len: $len" );
output( "finder_name: $finder_name ");


//---
Random::seed(112);


//--- replace a percentage of characters with underscores
$arr = str_split( $str );
$loops = 0;
while( $hide_characters > 0 ) {
	$index = Random::num(0, $len-1);

	while( $arr[$index] == '_' ) {
		$index++;
		if( $index >= $len ) {
			$index = 0;
		}

		$loops++;
		if( $loops > 100 ) {
			output( 'infinite loop' );
			break;
		}
	}
	$arr[ $index ] = '_';
	$hide_characters--;
}


//--- tell it to the world
$r = new stdClass();
$r->hint = join( '', $arr );
$r->finder_name = $finder_name;
$r_str = json_encode( $r );

file_put_contents( __DIR__ . '/../www/files/artifact_hint.txt', $r_str );
output( $r->hint );





//---
function output( $str ) {
	echo "* $str \n";
}


//--- pseudo random number generator
class Random {
	// random seed
	private static $RSeed = 0;
		// set seed
	public static function seed($s = 0) {
		self::$RSeed = abs(intval($s)) % 9999999 + 1;
		self::num();
	}
		// generate random number
	public static function num($min = 0, $max = 9999999) {
		if (self::$RSeed == 0) self::seed(mt_rand());
		self::$RSeed = (self::$RSeed * 125) % 2796203;
		return self::$RSeed % ($max - $min + 1) + $min;
	}
}

?>
