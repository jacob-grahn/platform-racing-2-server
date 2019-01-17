<?php

function demote_mod($user_name, $admin, $demoted_player)
{
    global $pdo, $server_name, $guild_owner;

    // safety first
    $html_name = htmlspecialchars($user_name, ENT_QUOTES);

    // make sure the admin is valid and online
    if (!isset($admin)) {
        output("CRITICAL FAILURE: An invalid user tried to demote $html_name.");
        return false;
    }

    // if the user isn't an admin on the server, kill the function (2nd line of defense)
    if ($admin->group !== 3) {
        output("$admin->name lacks the server power to demote $html_name.");
        $admin->write("message`Error: You lack the power to demote $html_name.");
        return false;
    }

    // don't let the admin demote the server owner
    if ($demoted_player->user_id === $guild_owner) {
        output("$admin->name lacks the power to demote the server owner.");
        $admin->write("message`Error: The server owner reigns supreme!");
        return false;
    }

    // let the server owner demote temps
    if ($admin->server_owner === true) {
        if (isset($demoted_player) && $demoted_player->temp_mod === true) {
            $demoted_player->group = 1;
            $demoted_player->temp_mod = false;
            $demoted_player->write('demoteMod`');
            $admin->write("message`$html_name has been demoted.");
            return true;
        } else {
            $admin->write("message`Error: $html_name is not a moderator on this server.");
            return false;
        }
    }

    try {
        // check for proper permission in the db (3rd + final line of defense before promotion)
        $admin_row = user_select($pdo, $admin->user_id);
        if ((int) $admin_row->power !== 3) {
            throw new Exception("You lack the power to demote $html_name.");
        }

        // get user info
        $user_row = user_select_by_name($pdo, $user_name);
        $user_id = (int) $user_row->user_id;

        // check if the person being demoted is an admin
        if ((int) $user_row->power === 3) {
            throw new Exception("You lack the power to demote $html_name, as they are an admin.");
        }

        // delete mod entry
        mod_power_delete($pdo, $user_id);

        // set power to 1
        user_update_power($pdo, $user_id, 1);

        // demote trial/perma mod and log it in the action log
        if ((int) $user_row->power >= 2) {
            // log action in action log
            $a_msg = "$admin->name demoted $user_name from $admin->ip on $server_name.";
            admin_action_insert($pdo, $admin->user_id, $a_msg, $admin->user_id, $admin->ip);

            // do it!
            if (isset($demoted_player) && $demoted_player->group >= 2) {
                $demoted_player->group = 1;
                $demoted_player->write('demoteMod`');
            }
            $admin->write("message`$html_name has been demoted.");
        } // demote temp mod
        elseif (isset($demoted_player) && $demoted_player->temp_mod === true && $demoted_player->group == 2) {
            $demoted_player->group = 1;
            $demoted_player->temp_mod = false;
            $demoted_player->trial_mod = false;
            $demoted_player->write('demoteMod`');
            $admin->write("message`$html_name has been demoted.");
        } else {
            throw new Exception("$user_name isn't a moderator.");
        }
    } catch (Exception $e) {
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES);
        echo "Error: $message";
        $admin->write("message`Error: $message");
        return false;
    }
}
