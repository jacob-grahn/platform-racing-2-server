<?php

function promote_to_moderator($name, $type, $admin, $promoted)
{
    global $pdo, $server_name;

    // safety first
    $html_name = htmlspecialchars($name);
    $html_type = htmlspecialchars($type);

    // sanity check: is the admin valid and online?
    if (!isset($admin)) {
        output("CRITICAL FAILURE: An invalid user tried to promote $html_name ".
            "to a $html_type moderator. Stopping the function.");
        return false;
    }

    // sanity check: if the user isn't an admin on the server or is a server owner,
    // kill the function (2nd line of defense)
    if ((int) $admin->group !== 3 || $admin->server_owner == true) {
        $admin->write("message`Error: You lack the power to promote $html_name to a $html_type moderator.");
        return false;
    }

    // sanity check: if the user being promoted is an admin, end the function
    if ((int) $promoted->group === 3) {
        $admin->write("message`Error: I'm not sure what would happen if you ".
            "promoted an admin to a moderator, but it would probably make the world explode.");
        return false;
    }

    // sanity check: validate mod type
    if ($type != 'temporary' && $type != 'trial' && $type != 'permanent') {
        $admin->write('message`Error: Unknown moderator type specified.');
        return false;
    }

    // sanity check: if promoting to a temp, make sure the user is online
    if ($type == 'temporary' && !isset($promoted)) {
        $admin->write("message`Could not find a user named \"$html_name\" on this server.");
        return false;
    }

    // vars
    $admin_id = $admin->user_id;
    $time = time();
    $min_time = $time - 21600; // 6 hours

    // get info about the user promoting
    $admin_row = user_select($pdo, $admin_id);

    // sanity check: if the user doesn't have proper permission in the db,
    // kill the function (3rd + final line of defense)
    if ((int) $admin_row->power !== 3) {
        $admin->write("message`Error: You lack the power to promote $html_name to a $html_type moderator.");
        return false;
    }

    // get info about the user being promoted
    $user_row = user_select_by_name($pdo, $name);
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
    if ($type == 'trial' || $type == 'permanent') {
        try {
            // throttle mod promotions
            $recent_promotion_count = promotion_log_count($pdo, $min_time);
            if ($recent_promotion_count > 0) {
                throw new Exception('Someone has already been promoted to a '.
                    'moderator recently. Wait a bit before trying to promote again.');
            }

            // log the power change
            $message = "'user_id: $user_id has been promoted to $type moderator.'";
            promotion_log_insert($pdo, $message, $time);

            // do the power change
            user_update_power($pdo, $user_id, 2);

            // set power limits
            if ($type == 'trial') {
                $max_ban = 86400; // 1 day
                $bans_per_hour = 30;
                $can_unpublish_level = 0;
            }
            if ($type == 'permanent') {
                $max_ban = 31536000; // 1 year
                $bans_per_hour = 101;
                $can_unpublish_level = 1;
            }

            // insert power limits into the db
            mod_power_insert($pdo, $user_id, $max_ban, $bans_per_hour, $can_unpublish_level);

            // action log
            $ip = $admin->ip;
            $admin_name = $admin->name;
            $admin_id = $admin->user_id;
            $promoted_name = $name;

            // log action in action log
            admin_action_insert(
                $pdo,
                $admin_id,
                "$admin_name promoted $promoted_name to a $type moderator from $ip on $server_name.",
                $admin_id,
                $ip
            );

            // make the server reflect the changes
            if (isset($promoted)) {
                $promoted->group = 2;
                $promoted->write('setGroup`2');
            }

            // tell the world
            $admin->write("message`$html_name has been promoted to a $html_type moderator!");
            return true;
        } catch (Exception $e) {
            $admin->write('message`Error: '.$e->getMessage());
            return false;
        }
    } // end if trial/permanent

    elseif ($type == 'temporary') {
        $promoted->becomeTempMod();
        $admin->write("message`$html_name has been promoted to a temporary moderator!");
        return true;
    } // end if temp
}
