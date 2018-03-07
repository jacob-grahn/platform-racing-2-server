<?php

require_once __DIR__ . '/db_fns.php';

function demote_mod($port, $user_name, $admin, $demoted_player) 
{
    global $db, $server_name, $guild_owner;
    
    // safety first
    $safe_user_name = htmlspecialchars($user_name);

    // if the user isn't an admin on the server, kill the function (2nd line of defense)
    if($admin->group != 3) {
        echo $admin->name." lacks the server power to demote $safe_user_name.";
        $admin->write("message`Error: You lack the power to demote $safe_user_name.");
        return false;
    }
    
    // don't let the admin demote the server owner
    if($demoted_player->user_id == $guild_owner) {
        echo $admin->name." lacks the power to demote the server owner.";
        $admin->write("message`Error: The server owner reigns supreme!");
        return false;
    }
    
    // let the server owner demote temps
    if ($admin->server_owner == true) {
        if (isset($demoted_player) && $demoted_player->temp_mod == true) {
            $demoted_player->group = 1;
            $demoted_player->write('setGroup`1');
            $demoted_player->temp_mod = false;
            $admin->write("message`$safe_user_name has been demoted.");
            return true;
        }
        else {
            $admin->write("message`$safe_user_name is not a moderator on this server.");
            return false;
        }
    }

    try {
        $user_id = name_to_id($db, $user_name);
        $safe_admin_id = addslashes($admin->user_id);
        $safe_user_id = addslashes($user_id);
        

        //check for proper permission in the db (3rd + final line of defense before promotion)
        $result = $db->query(
            "SELECT *
										FROM users
										WHERE user_id = '$safe_admin_id'
										LIMIT 0,1"
        );
        $row = $result->fetch_object();
        if($row->power != 3) {
            throw new Exception("You lack the power to demote $user_name.");
        }
        
        
        //check if the person being demoted is a staff member
        $user_result = $db->query(
            "SELECT *
										FROM users
										WHERE user_id = '$safe_user_id'
										LIMIT 0,1"
        );
        $user_row = $user_result->fetch_object();
        
        //delete mod entry
        $result = $db->query(
            "DELETE FROM mod_power
										WHERE user_id = '$safe_user_id'"
        );
        if(!$result) {
            throw new Exception("Could not delete the moderator type from the database because $user_name isn't a moderator.");
        }


        //set power to 1
        $result = $db->query(
            "UPDATE users
										SET power = 1
										WHERE user_id = '$safe_user_id'"
        );
        if(!$result) {
            throw new Exception("Could not demote $user_name due to a database error.");
        }
        
        // demote trial/perma mod and log it in the action log
        if($user_row->power >= 2) {
            // action log variables
            $ip = $admin->ip;
            $admin_id = $admin->user_id;
            $admin_name = $admin->name;
            $demoted_name = $user_name;
            
            // log action in action log
            $db->call('admin_action_insert', array($admin_id, "$admin_name demoted $demoted_name from $ip on $server_name.", $admin_id, $ip));
            
            // do it!
            if(isset($demoted_player) && $demoted_player->group >= 2) {
                $demoted_player->group = 1;
                $demoted_player->write('setGroup`1');
            }
            echo $admin->name." demoted $safe_user_name.";
            $admin->write("message`$safe_user_name has been demoted.");
        }
        // demote temp mod
        else if(isset($demoted_player) && $demoted_player->temp_mod === true && $demoted_player->group == 2) {
            $demoted_player->group = 1;
            $demoted_player->write('setGroup`1');
            $demoted_player->temp_mod = false;
            $admin->write("message`$safe_user_name has been demoted.");
        }
        else {
            throw new Exception("$user_name isn't a moderator.");
        }
        
    }

    catch(Exception $e){
        $message = htmlspecialchars($e->getMessage());
        echo "Error: $message";
        $admin->write("message`Error: $message");
        return false;
    }

}

?>
