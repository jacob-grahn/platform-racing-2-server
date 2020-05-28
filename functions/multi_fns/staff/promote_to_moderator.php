<?php

function promote_to_moderator($name, $type, $admin, $promoted)
{
    global $server_name;

    // safety first
    $html_name = htmlspecialchars($name, ENT_QUOTES);
    $html_type = htmlspecialchars($type, ENT_QUOTES);

    // make sure the admin is valid and online
    if (!isset($admin)) {
        output("CRITICAL FAILURE: An invalid user tried to promote $name to a $type moderator.");
        return false;
    }

    // if the user isn't an admin on the server or is a server owner, kill the function
    if ((int) $admin->group !== 3 || $admin->server_owner === true) {
        output("$admin->name lacks the server power to promote $name to a $type moderator.");
        $admin->write("message`Error: You lack the power to promote $html_name to a $html_type moderator.");
        return false;
    }

    // if the user being promoted is an admin, kill the function
    if (isset($promoted) && (int) $promoted->group === 3) {
        $err = 'I\'m not sure what would happen if you promoted an admin to a moderator, '
            .'but it would probably make the world explode.';
        $admin->write("message`Error: $err");
        return false;
    }

    // validate mod type
    if ($type !== 'temporary' && $type !== 'trial' && $type !== 'permanent') {
        $admin->write('message`Error: Unknown moderator type specified.');
        return false;
    }

    // make sure the user isn't a trial/perma mod being promoted to a trial mod
    if ($type === 'trial' && !$promoted->temp_mod && $promoted->group === 2) {
        $trial = $promoted->trial_mod ? ' trial' : '';
        $admin->write("message`Error: $html_name is already a$trial moderator.");
        return false;
    }

    // if promoting to a temp, make sure the user is online
    if ($type === 'temporary' && !isset($promoted)) {
        $admin->write("message`Error: Could not find a user named \"$html_name\" on this server.");
        return false;
    }

    // vars
    $time = time();
    $min_time = $time - 21600; // 6 hours

    // get info about the user promoting
    $admin_row = db_op('user_select', array($admin->user_id));

    // if the user doesn't have proper permission in the db, kill the function
    if ((int) $admin_row->power !== 3) {
        $admin->write("message`Error: You lack the power to promote $html_name to a $html_type moderator.");
        return false;
    }

    // get info about the user being promoted
    $user_row = db_op('user_select_by_name', array($name));
    $user_id = $user_row->user_id;

    // sanity check: if the user being promoted is a guest, end the function
    if ((int) $user_row->power < 1) {
        $admin->write("message`Error: Guests can't be promoted to moderators.");
        return false;
    }

    // sanity check: if the user being promoted is an admin, kill the function
    if ((int) $user_row->power === 3) {
        $admin->write("message`Error: I'm not sure what would happen if you ".
            "promoted an admin to a moderator, but it would probably make ".
            "the world explode.");
        return false;
    }

    // now that we've determined that the user is able to do what they're trying to do, let's finish
    // if type is trial or permanent, do promotion things in the db
    if ($type === 'trial' || $type === 'permanent') {
        try {
            // throttle mod promotions
            if (db_op('promotion_log_count', array($min_time)) > 0) {
                $msg = 'Someone has already been promoted to a moderator recently. '
                    .'Wait a bit before trying to promote again.';
                throw new Exception($msg);
            }

            // log the power change
            $msg = "$user_id has been promoted to $type moderator by $admin->name.";
            db_op('promotion_log_insert', array($msg, $time));

            // do the power change
            db_op('user_update_power', array($user_id, 2, $type === 'trial'));

            // set power limits
            if ($type === 'trial') {
                $max_ban = 86400;
                $bans_per_hour = 30;
                $can_unpublish_level = 0;
            } elseif ($type === 'permanent') {
                $max_ban = 31536000;
                $bans_per_hour = 101;
                $can_unpublish_level = 1;
            }

            // insert power limits into the db
            db_op('mod_power_insert', array($user_id, $max_ban, $bans_per_hour, $can_unpublish_level));

            // log action in admin action log
            $a_msg = "$admin->name promoted $user_row->name to a $type moderator from $admin->ip on $server_name.";
            db_op('admin_action_insert', array($admin->user_id, $a_msg, $admin->user_id, $admin->ip));

            // update everyone (server, client menus)
            if (isset($promoted)) {
                $promoted->group = 2;
                $promoted->temp_mod = false;
                if ($type === 'trial') {
                    $promoted->trial_mod = true;
                    $promoted->write('becomeTrialMod`');
                } elseif ($type === 'permanent') {
                    $promoted->trial_mod = false;
                    $promoted->write('becomeFullMod`');
                }
            }

            // tell the world
            $admin->write("message`$html_name has been promoted to a $html_type moderator!");
            return true;
        } catch (Exception $e) {
            $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
            $admin->write("message`Error: $error");
            return false;
        }
    } elseif ($type === 'temporary') {
        if ($promoted->group < 2) {
            $promoted->becomeTempMod();
            $admin->write("message`$html_name has been promoted to a temporary moderator!");
            return true;
        } else {
            $trial = $promoted->trial_mod ? ' trial' : '';
            $admin->write("message`Error: $html_name is already a$trial moderator.");
        }
    }
}
