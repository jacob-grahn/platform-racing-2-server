<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$user_id = find('id');
$action = find('action', 'lookup');

try {

    //connect
    $db = new DB();


    //make sure you're an admin
    $mod = check_moderator($db, true, 3);


    //lookup
    if($action === 'lookup') {
	     output_form($db, $user_id);
    }


    //update
    if($action === 'update') {
	     update($db);
    }


} catch (Exception $e) {
    output_header('Update PR2 Account', true);
    echo 'error=' . ($e->getMessage());
    output_footer();
}












function output_form($db, $user_id) {

    output_header('Update PR2 Account', true);

    echo '<form name="input" action="update_account.php" method="get">';

	$user = $db->grab_row('user_select', array($user_id));
	$pr2 = $db->grab_row('pr2_select', array($user->user_id));
	$pr2_epic = $db->grab_row('epic_upgrades_select', array($user->user_id), '', true);
	echo "user_id: $user->user_id <br>---<br>";


	echo 'Name: <input type="text" size="" name="name" value="'.htmlspecialchars($user->name).'"><br>';
	echo 'Email: <input type="text" name="email" value="'.htmlspecialchars($user->email).'"><br>';
	echo 'Guild: <input type="text" name="guild" value="'.htmlspecialchars($user->guild).'"><br>';
	echo 'Hats: <input type="text" size="100" name="hats" value="'.$pr2->hat_array.'"><br>';
	echo 'Heads: <input type="text" size="100" name="heads" value="'.$pr2->head_array.'"><br>';
	echo 'Bodies: <input type="text" size="100" name="bodies" value="'.$pr2->body_array.'"><br>';
	echo 'Feet: <input type="text" size="100" name="feet" value="'.$pr2->feet_array.'"><br>';
	if($pr2_epic) {
	    echo 'Epic Hats: <input type="text" size="100" name="eHats" value="'.$pr2_epic->epic_hats.'"><br>';
	    echo 'Epic Heads: <input type="text" size="100" name="eHeads" value="'.$pr2_epic->epic_heads.'"><br>';
	    echo 'Epic Bodies: <input type="text" size="100" name="eBodies" value="'.$pr2_epic->epic_bodies.'"><br>';
	    echo 'Epic Feet: <input type="text" size="100" name="eFeet" value="'.$pr2_epic->epic_feet.'"><br>';
	}
	echo '<input type="hidden" name="action" value="update">';
	echo '<input type="hidden" name="id" value="'.$user->user_id.'">';

    echo '<br/>';
    echo '<input type="submit" value="Submit">';
    echo '</form>';

    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>// hats
NONE = 1
EXP = 2
KONG = 3
PROPELLER = 4
COWBOY = 5
CROWN = 6
SANTA = 7
PARTY = 8
TOP_HAT = 9
JUMP_START = 10
MOON = 11
THIEF = 12
JIGG = 13
ARTIFACT = 14

// heads
CLASSIC = 1
TIRED = 2
SMILER = 3
FLOWER = 4
CLASSIC_GIRL = 5
GOOF = 6
DOWNER = 7
BALLOON = 8
WORM = 9
UNICORN = 10
BIRD = 11
SUN = 12
CANDY = 13
INVISIBLE = 14
FOOTBALL_HELMET = 15
BASKETBALL = 16
STICK = 17
CAT = 18
ELEPHANT = 19
ANT = 20
ASTRONAUT = 21
ALIEN = 22
DINO = 23
ARMOR = 24
FAIRY = 25
GINGERBREAD = 26
BUBBLE = 27
KING = 28
QUEEN = 29
SIR = 30
VERY_INVISIBLE = 31
TACO = 32
SLENDER = 33
Santa = 34
Frost Djinn = 35
Reindeer = 36
Crocodile = 37
Valentine = 38
Rabbit = 39

// bodies
CLASSIC = 1
STRAP = 2
DRESS = 3
PEC = 4
GUT = 5
COLLAR = 6
MISS_PR2 = 7
BELT = 8
SNAKE = 9
BIRD = 10
INVISIBLE = 11
BEE = 12
STICK = 13
CAT = 14
CAR = 15
ELEPHANT = 16
ANT = 17
ASTRONAUT = 18
ALIEN = 19
GALAXY = 20
BUBBLE = 21
DINO = 22
ARMOR = 23
FAIRY = 24
GINGERBREAD = 25
KING = 26
QUEEN = 27
SIR = 28
FRED = 29
VERY_INVISIBLE = 30
TACO = 31
SLENDER = 32
Santa = 34
Frost Djinn = 35
Reindeer = 36
Crocodile = 37
Valentine = 38
Rabbit = 39

// feet
CLASSIC = 1
HEEL = 2
LOAFER = 3
SOCCER = 4
MAGNET = 5
TINY = 6
SANDAL = 7
BARE = 8
NICE = 9
BIRD = 10
INVISIBLE = 11
STICK = 12
CAT = 13
TIRE = 14
ELEPHANT = 15
ANT = 16
ASTRONAUT = 17
ALIEN = 18
GALAXY = 19
DINO = 20
ARMOR = 21
FAIRY = 22
GINGERBREAD = 23
KING = 24
QUEEN = 25
SIR = 26
VERY_INVISIBLE = 27
BUBBLE = 28
TACO = 29
SLENDER = 30
Santa = 34
Frost Djinn = 35
Reindeer = 36
Crocodile = 37
Valentine = 38
Rabbit = 39</pre>';

    output_footer();
}



function update($db) {
    $guild_id = (int) find('guild');
    $user_id = (int) find('id');

    $user = $db->grab_row('user_select', array($user_id));
    $email = find('email');

    if($user->email !== $email) {
	$code = 'manual-' . time();
	$db->call('changing_email_insert', array($user_id, $user->email, $email, $code, ''));
	$change_id = $db->grab('change_id', 'changing_email_select', array($code));
	$db->call('changing_email_complete', array($change_id, ''));
    }

    $db->call(
	    'user_update',
	    array(
		$user_id,
		find('name'),
		find('email'),
		$guild_id,
		find('hats'),
		find('heads'),
		find('bodies'),
		find('feet'),
		find('eHats'),
		find('eHeads'),
		find('eBodies'),
		find('eFeet')
	    )
    );

    header("Location: player_deep_info.php?name1=" . urlencode(find('name')));
}

?>
