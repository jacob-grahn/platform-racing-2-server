<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$guild_id = find('guild_id', 0);

output_header('Guild Deep Info', true);


try{
	
	//connect
	$db = new DB();
	
	
	//make sure you're an admin
	$mod = check_moderator($db, false, 3);
	
	if ($guild_id == 0) {
		$guild_id = '';
	}
	
	
	echo '<form name="input" action="" method="get">';
	echo 'Guild ID: <input type="text" name="guild_id" value="'.htmlspecialchars($guild_id).'"><br>';
	if( $guild_id != '' ) {
		try {
			$guild = $db->grab_row( 'guild_select', array($guild_id), 'Could not find a guild with that id.' );
			$members = $db->call( 'guild_select_members', array($guild_id) );
			output_object( $guild );
			output_objects( $members );
			echo '<a href="//pr2hub.com/mod/update_guild.php?guild_id='.$guild->guild_id.'">edit</a><br><br><br>';
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
