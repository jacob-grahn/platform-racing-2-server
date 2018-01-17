<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$guild_id = find('id', 0);

output_header('Guild Deep Info');


try{
	
	//connect
	$db = new DB();
	
	
	//make sure you're an admin
	$mod = check_moderator($db, false, 3);
	
	
	//make it easy to get around
	output_mod_navigation();
	
	
	echo '<form name="input" action="" method="get">';
	echo 'Guild ID: <input type="text" name="guild_id" value="'.htmlspecialchars($guild_id).'"><br>';
	if( $guild_id != '' ) {
		try {
			$guild = $db->grab_row( 'guild_select', array($guild_id) );
			$members = $db->grab_row( 'guild_select_members', array($guild->guild_id), '', true );
			echo "Guild ID: $guild->guild_id <br/>";
			output_object( $guild );
			output_objects( $members );
			echo '<a href="//pr2hub.com/mod/update_guild.php?id='.$guild->guild_id.'">edit</a><br><br><br>';
		}

		catch(Exception $e) {
			echo "<i>".$e->getMessage()."</i><br><br>";
		}
	}
	
	echo '<input type="submit" value="Submit">';
	echo '</form>';

}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

output_footer();

function output_objects( $objs ) {
	foreach( $objs as $obj ) {
		output_object( $obj, ', ' );
		echo '<br/>';
	}
}

function output_object( $obj, $sep='<br/>' ) {
	foreach( $obj as $var=>$val ) {
		if( $var != 'guild_id' ) {
			echo "$var: ".htmlspecialchars($val)."$sep";
		}
	}
}

?>
