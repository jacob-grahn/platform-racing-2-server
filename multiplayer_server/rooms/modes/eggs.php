<?php

function sort_finish_array_egg($a, $b){
	if( $a->eggs < $b->eggs ) {
		return 1;
	}
	else if( $a->eggs > $b->eggs ) {
		return -1;
	}
	else {
		return 0;
	}
}

?>
