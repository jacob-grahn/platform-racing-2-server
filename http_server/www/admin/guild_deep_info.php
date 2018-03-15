<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

$guild_id = find('guild_id', 0);

try {
    //connect
    $db = new DB();
    $pdo = pdo_connect();

    //make sure you're an admin
    $mod = check_moderator($pdo, false, 3);

    if ($guild_id == 0) {
        $guild_id = '';
    }

    output_header('Guild Deep Info', true, true);


    echo '<form name="input" action="" method="get">';
    echo 'Guild ID: <input type="text" name="guild_id" value="'.htmlspecialchars($guild_id).'">&nbsp;';
    echo '<input type="submit" value="Submit"><br>';
    if ($guild_id != '') {
        try {
            $guild = $db->grab_row('guild_select', array($guild_id), 'Could not find a guild with that id.');
            $owner_transfers = $db->to_array($db->call('guild_transfers_select_by_guild', array($guild->guild_id)));
            $members = $db->call('guild_select_members', array($guild_id));
            output_object($guild);
            output_objects($owner_transfers);
            output_objects($members);
            echo '<a href="update_guild.php?guild_id='.$guild->guild_id.'">edit</a><br><br><br>';
        } catch (Exception $e) {
            echo "<i>Error: ".$e->getMessage()."</i><br><br>";
        }
    }

    echo '</form>';
    output_footer();
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage();
    output_footer();
}

function output_objects($objs)
{
    foreach ($objs as $obj) {
        output_object($obj, ', ');
        echo '<br/>';
    }
}

function output_object($obj, $sep = '<br/>')
{
    foreach ($obj as $var => $val) {
        if ($var != 'guild_id') {
            echo "$var: ".htmlspecialchars($val)."$sep";
        }
    }
}
