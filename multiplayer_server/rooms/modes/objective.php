<?php

//--- more objectives makes the winner. objective ties are determined by finish times ------------
function sort_finish_array_objective($a, $b){
	$ao = count( $a->objectives_reached );
	$bo = count( $b->objectives_reached );
	$at = $a->last_objective_time;
	$bt = $b->last_objective_time;
	
	if( $ao < $bo ) {
		return 1;
	}
	else if( $ao > $bo ) {
		return -1;
	}
	else {
		if( $at < $bt ) {
			return -1;
		}
		else if( $at > $bt ) {
			return 1;
		}
		else {
			return 0;
		}
	}
}

?>
