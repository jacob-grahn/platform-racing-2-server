<?php

require_once('../../fns/all_fns.php');

$rank = find( 'rank', 0 );
$exp = find( 'exp', 0 );
$tot_exp = $exp;

$exp_points = 30;
for($i=1; $i<100; $i++){
	${'rank'.$i} = $exp_points;
	$exp_points *= 1.25;
}

for($j=1; $j<=$rank; $j++) {
	output( ${'rank'.$j} );
	$tot_exp += round( ${'rank'.$j} );
}

output( '' );
output( "imported rank: $rank" );
output( "imported exp: $exp" );

output( '' );
output( "total exp: $tot_exp" );

$half_exp = round( $tot_exp/2 );
output( "half exp: $half_exp" );

function output( $str ) {
	echo "* " . strip_tags($str, "<br><br/>") . "<br/>";
}

?>
