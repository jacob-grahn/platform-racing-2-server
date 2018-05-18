<?php

function order_placed_handler($pdo, $request)
{
    $recipient_id = $request->recipient_id; //The id of the user to receive the items.
    $order_id = $request->order_id; // A unique order id for this order in our database.
    $order_info = $request->order_info; //The order info string you passed into purchaseItemsRemote
    list($pr2_user_id, $slug) = explode(',', $order_info);

    //--- check that the item is available
    $descs = describeVault($pdo, $pr2_user_id, array($slug));
    $desc = $descs[0];
    if ($desc->available == false) {
        throw new Exception('This item is no longer available');
    }

    //--- apply item to player's account
    $user = user_select_expanded($pdo, $pr2_user_id);
    unlock_item(
        $pdo,
        $pr2_user_id,
        $user->guild,
        $user->server_id,
        $slug,
        $user->name,
        $recipient_id,
        $order_id,
        $desc->title
    );

    //--- tell it
    $reply = new stdClass();
    $reply->state = 'completed'; //$reply->state = 'canceled';
    return( $reply );
}


function unlock_item($pdo, $user_id, $guild_id, $server_id, $slug, $user_name, $kong_user_id, $order_id, $title)
{
    error_log("unlock_item: $user_id, $guild_id, $server_id, $slug, $user_name, $kong_user_id, $order_id");
    purchase_insert($pdo, $user_id, $guild_id, $slug, $kong_user_id, $order_id);
    $command = "unlock_perk`$slug`$user_id`$guild_id`$user_name";
    $reply = '';
    $target_servers = array();

    if ($slug == 'guild-fred') {
        $reply = "Fred smiles on you!";
    } elseif ($slug == 'guild-ghost') {
        $reply = "Ninja mode: engage!";
    } elseif ($slug == 'guild-artifact') {
        $reply = "Ultimate power, courtesy of Fred!";
    } elseif ($slug == 'king-set') {
        award_part($pdo, $user_id, 'head', 28);
        award_part($pdo, $user_id, 'body', 26);
        award_part($pdo, $user_id, 'feet', 24);
        award_part($pdo, $user_id, 'eHead', 28);
        award_part($pdo, $user_id, 'eBody', 26);
        award_part($pdo, $user_id, 'eFeet', 24);
        $command = "unlock_set_king`$user_id";
        $reply = "The Wise King set has been added your account!";
    } elseif ($slug == 'queen-set') {
        award_part($pdo, $user_id, 'head', 29);
        award_part($pdo, $user_id, 'body', 27);
        award_part($pdo, $user_id, 'feet', 25);
        award_part($pdo, $user_id, 'eHead', 29);
        award_part($pdo, $user_id, 'eBody', 27);
        award_part($pdo, $user_id, 'eFeet', 25);
        $command = "unlock_set_queen`$user_id";
        $reply = "The Wise Queen set has been added your account!";
    } elseif ($slug == 'djinn-set') {
        award_part($pdo, $user_id, 'head', 35);
        award_part($pdo, $user_id, 'body', 35);
        award_part($pdo, $user_id, 'feet', 35);
        award_part($pdo, $user_id, 'eHead', 35);
        award_part($pdo, $user_id, 'eBody', 35);
        award_part($pdo, $user_id, 'eFeet', 35);
        $command = "unlock_set_djinn`$user_id";
        $reply = "The Frost Djinn set has been added your account!";
    } elseif ($slug == 'epic-everything') {
        award_part($pdo, $user_id, 'eHat', '*');
        award_part($pdo, $user_id, 'eHead', '*');
        award_part($pdo, $user_id, 'eBody', '*');
        award_part($pdo, $user_id, 'eFeet', '*');
        $command = "unlock_epic_everything`$user_id";
        $reply = "All Epic Upgrades are yours!";
    } elseif ($slug == 'happy-hour') {
        $target_servers = array( $server_id );
        $reply = "This is the happiest hour ever!";
    } elseif ($slug == 'server-1-day' || $slug == 'server-30-days') {
        $command = '';
        $seconds = 0;
        if ($slug == 'server-1-day') {
            $seconds = 60*60*24;
        }
        if ($slug == 'server-30-days') {
            $seconds = 60*60*24*30;
        }

        $result = create_server($pdo, $guild_id, $seconds);

        if ($result == 0) {
            throw new Exception('Could not start the server.');
        }
        if ($result == 1) {
            $reply = 'The best server ever is starting up! ETA 2 minutes.';
        }
        if ($result == 2) {
            $reply = 'The life of your private server has been extended! Long live your guild!';
        }
    } elseif ($slug == 'rank-rental') {
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

    if ($command != '') {
        poll_servers($servers, $command, false, $target_servers);
    }
    if ($reply != '') {
        $obj = new stdClass();
        $obj->user_id = $user_id;
        $obj->message = $reply;
        $data = json_encode($obj);
        poll_servers($servers, "send_message_to_player`$data", false, array($server_id));
    }

    send_confirmation_pm($pdo, $user_id, $title, $order_id);

    return $reply;
}


function send_confirmation_pm($pdo, $user_id, $title, $order_id)
{
    $pm = "Thank you for your support! This PM is to confirm your order.
item: $title
order id: $order_id";
    message_insert($pdo, $user_id, 1, $pm, '0');
}


function create_server($pdo, $guild_id, $seconds_of_life)
{
    global $COMM_PASS;
    $existing_server = server_select_by_guild_id($pdo, $guild_id);
    $guild = guild_select($pdo, $guild_id);
    $port = 1 + servers_select_highest_port($pdo);
    $server_name = $guild->guild_name;
    $address = 'assign';
    $expire_time = time() + $seconds_of_life;
    $salt = $COMM_PASS;
    $guild_id = $guild->guild_id;

    if (!$existing_server) {
        server_insert($pdo, $server_name, $address, $port, $expire_time, $salt, $guild_id);
        return( 1 );
    } else {
        $server_id = $existing_server->server_id;
        $expire_time_2 = strtotime($existing_server->expire_date) + $seconds_of_life;
        if ($expire_time_2 > $expire_time) {
            $expire_time = $expire_time_2;
        }
        server_update_expire_date($pdo, $server_id, $expire_time, $server_name);
        return( 2 );
    }
}
