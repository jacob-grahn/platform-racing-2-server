<?php


function order_placed_handler($pdo, $request)
{
    $recipient_id = $request->recipient_id; // the id of the user to receive the items.
    $order_id = $request->order_id; // a unique order id for this order in our database.
    $order_info = $request->order_info; // the order info string you passed into purchaseItemsRemote
    list($pr2_user_id, $slug) = explode(',', $order_info);
    $pr2_user_id = (int) $pr2_user_id;

    // check that the item is available
    $descs = describeVault($pdo, $pr2_user_id, array($slug));
    $desc = $descs[0];
    if ($desc->available == false) {
        throw new Exception('This item is no longer available.');
    }

    // apply item to player's account
    $user = user_select_expanded($pdo, $pr2_user_id);
    $guild = (int) $user->guild;
    $server = (int) $user->server_id;
    $pr2_name = $user->name;
    unlock_item($pdo, $pr2_user_id, $guild, $server, $slug, $pr2_name, $recipient_id, $order_id, $desc->title);

    // tell it
    $reply = new stdClass();
    $reply->state = 'completed'; // $reply->state = 'canceled';
    return $reply;
}


function unlock_item($pdo, $user_id, $guild_id, $server_id, $slug, $user_name, $kong_user_id, $order_id, $title)
{
    error_log("unlock_item: $user_id, $guild_id, $server_id, $slug, $user_name, $kong_user_id, $order_id");
    purchase_insert($pdo, $user_id, $guild_id, $slug, $kong_user_id, $order_id);
    $command = "unlock_perk`$slug`$user_id`$guild_id`$user_name";
    $reply = '';
    $target_servers = array();

    if ($slug === 'guild-fred') {
        $reply = 'Fred smiles on you!';
    } elseif ($slug === 'guild-ghost') {
        $reply = 'Ninja mode: engage!';
    } elseif ($slug === 'guild-artifact') {
        $reply = 'Ultimate power, courtesy of Fred!';
    } elseif ($slug === 'king-set') {
        award_part($pdo, $user_id, 'head', 28);
        award_part($pdo, $user_id, 'body', 26);
        award_part($pdo, $user_id, 'feet', 24);
        award_part($pdo, $user_id, 'eHead', 28);
        award_part($pdo, $user_id, 'eBody', 26);
        award_part($pdo, $user_id, 'eFeet', 24);
        $command = "unlock_set_king`$user_id";
        $reply = 'The Wise King set has been added your account!';
    } elseif ($slug === 'queen-set') {
        award_part($pdo, $user_id, 'head', 29);
        award_part($pdo, $user_id, 'body', 27);
        award_part($pdo, $user_id, 'feet', 25);
        award_part($pdo, $user_id, 'eHead', 29);
        award_part($pdo, $user_id, 'eBody', 27);
        award_part($pdo, $user_id, 'eFeet', 25);
        $command = "unlock_set_queen`$user_id";
        $reply = 'The Wise Queen set has been added your account!';
    } elseif ($slug === 'djinn-set') {
        award_part($pdo, $user_id, 'head', 35);
        award_part($pdo, $user_id, 'body', 35);
        award_part($pdo, $user_id, 'feet', 35);
        award_part($pdo, $user_id, 'eHead', 35);
        award_part($pdo, $user_id, 'eBody', 35);
        award_part($pdo, $user_id, 'eFeet', 35);
        $command = "unlock_set_djinn`$user_id";
        $reply = 'The Frost Djinn set has been added your account!';
    } elseif ($slug === 'epic-everything') {
        award_part($pdo, $user_id, 'eHat', '*');
        award_part($pdo, $user_id, 'eHead', '*');
        award_part($pdo, $user_id, 'eBody', '*');
        award_part($pdo, $user_id, 'eFeet', '*');
        $command = "unlock_epic_everything`$user_id";
        $reply = 'All Epic Upgrades are yours!';
    } elseif ($slug === 'happy-hour') {
        $target_servers = array($server_id);
        $reply = 'This is the happiest hour ever!';
    } elseif ($slug === 'server-1-day' || $slug === 'server-30-days') {
        $command = '';
        $days = (int) explode('-', $slug)[1];
        $result = create_server($pdo, $guild_id, $days);

        if ($result->status_code === 0) {
            throw new Exception('An error occurred. Please notify a member of the PR2 staff team for assistance.');
        } elseif ($result->status_code === 1) {
            $reply = "The best server ever is starting up! ETA 2 minutes."
                ."\n\n(Expiration time: ";
        } elseif ($result->status_code === 2) {
            $reply = 'The life of your private server has been extended! Long live your guild!'
                ."\n\n(New expiration time: ";
            $command = "extend_server_life`$guild_id`$result->new_time";
        }

        $reply .= date('F j, Y \a\t g:ia', $result->new_time) . ' GMT)';
    } elseif ($slug === 'rank-rental') {
        rank_token_rental_insert($pdo, $user_id, $guild_id);

        $obj = new stdClass();
        $obj->user_id = $user_id;
        $obj->guild_id = $guild_id;
        $data = json_encode($obj);

        $command = "unlock_rank_token_rental`$data";
        $reply = 'You just got a rank token!';
    } else {
        throw new Exception("Item not found: " . strip_tags($slug, '<br>'));
    }

    $servers = servers_select($pdo);

    if (!empty($command)) {
        poll_servers($servers, $command, false, $target_servers);
    }
    if (!empty($reply)) {
        $obj = new stdClass();
        $obj->user_id = $user_id;
        $obj->message = $reply;
        $data = json_encode($obj);
        poll_servers($servers, "message_player`$data", false, array($server_id));
    }

    send_confirmation_pm($pdo, $user_id, $title, $order_id);
    return $reply;
}


function send_confirmation_pm($pdo, $user_id, $title, $order_id)
{
    $pm = "Thank you for your support! This PM is to confirm your order.\nItem: $title\nOrder ID: $order_id";
    message_insert($pdo, $user_id, 1, $pm, '0');
}


function create_server($pdo, $guild_id, $days_of_life)
{
    // existing server info
    $existing_server = server_select_by_guild_id($pdo, $guild_id);
    $port = servers_select_highest_port($pdo) + 1;

    // guild info
    $guild = guild_select($pdo, $guild_id);
    $guild_id = (int) $guild->guild_id;
    $server_name = $guild->guild_name;

    $ret = new stdClass();
    $ret->status_code = 0;
    try {
        // ...time after time
        $life_secs = 86400 * $days_of_life;
        $life_from_now = time() + $life_secs;

        if (!$existing_server) {
            global $SERVER_IP;
            $server_id = server_insert($pdo, $life_from_now, $server_name, $SERVER_IP, $port, $guild_id);
            start_server(PR2_ROOT . '/pr2.php', $port, $server_id);
            $ret->new_time = $life_from_now;
            $ret->status_code = 1;
        } else {
            $server_id = $existing_server->server_id;
            $life_from_expiry = strtotime($existing_server->expire_date) + $life_secs;
            $life_from_expiry = $life_from_expiry < $life_from_now ? $life_from_now : $life_from_expiry;
            server_update_expire_date($pdo, $life_from_expiry, $server_id);
            $ret->new_time = $life_from_expiry;
            $ret->status_code = 2;
        }
    } catch (Exception $e) {
        unset($e);
    } finally {
        return $ret;
    }
}
