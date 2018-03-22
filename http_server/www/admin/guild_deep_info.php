<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/guilds/guild_select.php';
require_once __DIR__ . '/../../queries/guilds/guild_select_members.php';
require_once __DIR__ . '/../../queries/guild_transfers/guild_transfers_select_by_guild.php';

$guild_id = find('guild_id', 0);

try {
    // rate limiting
    rate_limit('guild-deep-info-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('guild-deep-info-'.$ip, 5, 2);
    
    //connect
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
            $guild = guild_select($pdo, $guild_id);
            $owner_transfers = guild_transfers_select_by_guild($pdo, $guild_id, true);
            $members = guild_select_members($pdo, $guild_id, true);
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
    if ($objs !== false) {
        foreach ($objs as $obj) {
            output_object($obj, ', ');
            echo '<br/>';
        }
    }
}

function output_object($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($var == 'name') {
                $safe_val = htmlspecialchars($val);
                $url_val = urlencode($val);
                $val = "<a href='player_deep_info.php?name1=$url_val'>$safe_val</a>";
                echo "$var: $val".$sep;
            }
            if ($var != 'guild_id' && $var != 'name') {
                echo "$var: ".htmlspecialchars($val)."$sep";
            }
        }
    }
}
