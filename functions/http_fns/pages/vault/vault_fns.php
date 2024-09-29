<?php


// populate descriptions for vault items
function describeVault($pdo, $user, $items_to_get = 'all')
{
    // gather user info
    if (is_int($user)) {
        $user = user_select_expanded($pdo, $user);
    }
    $server = server_select($pdo, $user->server_id);
    $guild = $user->guild != 0 ? guild_select($pdo, $user->guild) : false;

    // get requested items
    $vault_info = file_get_contents(CACHE_DIR . '/vault.json');
    if (!$vault_info) {
        regenerate_vault_items($pdo);
        $vault_info = file_get_contents(CACHE_DIR . '/vault.json');
        if (!$vault_info) {
            throw new Exception('Could not retrieve vault info.');
        }
    }

    // populate array
    $vault_info = json_decode($vault_info);
    $items = $items_to_get === 'all' ? $vault_info->listings : new stdClass();
    if ($items_to_get !== 'all') {
        foreach ($items_to_get as $slug) {
            if (isset($vault_info->listings->$slug)) {
                $items->$slug = $vault_info->listings->$slug;
                continue;
            }
            throw new Exception('Invalid item specified.');
        }
    }

    // check item availablity
    $listings = [];
    foreach ($items as $slug => $item) {
        $item->max_quantity = (int) $item->max_quantity; // quick typecast
        $item->available = false;
        if ($slug === 'stats_boost') {
            $item->available = $server->tournament == 0 && !apcu_exists("sb-$user->user_id-" . round(time() / 86400));
        } elseif ($slug === 'happy_hour') {
            $item->available = $server->tournament == 0;
        } elseif ($slug === 'rank_rental') {
            $item->rented_tokens = rank_token_rentals_count($pdo, $user->user_id, $user->guild);
            $rt_lang = $item->rented_tokens > 0 ? 'another' : 'a';
            $item->available = $item->rented_tokens < 21;
            $item->price = 50 + (20 * $item->rented_tokens);
            $item->description = "You and your guild gain $rt_lang rank token for a week.";
        } elseif ($slug === 'king_set') {
            $item->available = array_search(28, explode(',', $user->head_array)) === false;
        } elseif ($slug === 'queen_set') {
            $item->available = array_search(29, explode(',', $user->head_array)) === false;
        } elseif ($slug === 'djinn_set') {
            $item->available = array_search(35, explode(',', $user->head_array)) === false;
        } elseif ($slug === 'server_1_day' || $slug === 'server_30_days') {
            if ($guild && $guild->owner_id == $user->user_id) {
                $item->available = true;
            } else {
                $item->faq .= "\n\n<b>Why can't I create a private server?</b>\nThis option is for guild owners only!";
            }
        } elseif ($slug == 'epic_everything') {
            $item->available = array_search('*', explode(',', $user->epic_heads)) === false;
        } else {
            $item->available = true;
        }

        $listings[] = $item;
    }

    // tell the world
    $ret = new stdClass();
    $ret->info = $vault_info->info;
    $ret->info->retrieved = time();
    $ret->listings = $listings;
    return $ret;
}


function vault_purchase_item($pdo, $user, $item, $price, $quantity = 1)
{
    global $coins_deducted;

    $slug = $item->slug;
    $user_id = (int) $user->user_id;
    $guild_id = (int) $user->guild;

    // do the purchase
    $order_id = (int) vault_purchase_insert($pdo, $user_id, $guild_id, $slug, $price, $quantity);
    if ($order_id <= 0) {
        throw new Exception('Unable to complete vault purchase.');
    }

    // deduct the coins from the buyer's account
    user_update_coins($pdo, $user_id, 0 - $price);
    $coins_deducted = $price;

    // communication w/ server
    $command = "unlock_perk`$slug`$user_id`$guild_id`$user->name`$quantity";
    $reply = '';
    $target_servers = [];

    // handle items
    if ($slug === 'guild_fred') {
        $reply = 'Fred smiles on you!';
    } elseif ($slug === 'guild_ghost') {
        $reply = 'Ninja mode: engage!';
    } elseif ($slug === 'guild_artifact') {
        $reply = 'Ultimate power, courtesy of Fred!';
    } elseif ($slug === 'happy_hour') {
        $target_servers = [$user->server_id];
        $reply = 'These will be the happiest ' . ($quantity > 1 ? $quantity . ' hours' : 'hour') . ' ever!';
    } elseif ($slug === 'king_set') {
        unlock_set($pdo, $user_id, [28, 26, 24]);
        $command = "unlock_set_king`$user_id";
        $reply = 'The Wise King set has been added your account!';
    } elseif ($slug === 'queen_set') {
        unlock_set($pdo, $user_id, [29, 27, 25]);
        $command = "unlock_set_queen`$user_id";
        $reply = 'The Wise Queen set has been added your account!';
    } elseif ($slug === 'djinn_set') {
        unlock_set($pdo, $user_id, [35, 35, 35]);
        $command = "unlock_set_djinn`$user_id";
        $reply = 'The Frost Djinn set has been added your account!';
    } elseif ($slug === 'epic_everything') {
        unlock_set($pdo, $user_id, 'epic_everything');
        $command = "unlock_epic_everything`$user_id";
        $reply = 'All Epic Upgrades are yours!';
    } elseif ($slug === 'server_1_day' || $slug === 'server_30_days') {
        $command = '';
        $days = $quantity * ((int) explode('_', $slug)[1]);
        $result = create_server($pdo, $guild_id, $days);

        if ($result->status_code === 0) {
            throw new Exception('An error occurred. Please notify a member of the PR2 staff team for assistance.');
        } elseif ($result->status_code === 1) {
            $reply = 'The best server ever is starting up! It\'ll be ready in about 2 minutes.'
                ."\n\n(Expiration time: ";
        } elseif ($result->status_code === 2) {
            $reply = 'The life of your private server has been extended! Long live your guild!'
                ."\n\n(New expiration time: ";
        }

        $command = "extend_server_life`$guild_id`$result->new_time";
        $reply .= date('F j, Y \a\t g:ia T', $result->new_time) . ')';
    } elseif ($slug === 'rank_rental') {
        rank_token_rental_insert($pdo, $user_id, $guild_id, $quantity);

        $obj = new stdClass();
        $obj->user_id = $user_id;
        $obj->guild_id = $guild_id;
        $obj->quantity = $quantity;
        $data = json_encode($obj);

        $command = "unlock_rank_token_rental`$data";
        $reply = 'You just got ' . ($quantity === 1 ? 'a rank token' : "$quantity rank tokens") . '!';
    } else {
        throw new Exception("Item not found: " . strip_tags($slug, '<br>'));
    }

    // send item command to the server
    if (!empty($command)) {
        @poll_servers(servers_select($pdo), $command, false, isset($target_servers) ? $target_servers : []);
    }

    // get active purchase (to calculate start time)
    $active_purchase = vault_purchase_select_active($pdo, $slug, $user_id, $guild_id);
    $start_time = !empty($active_purchase) ? $active_purchase->start_time + ($quantity * 3600) : time();

    // complete
    vault_purchase_complete($pdo, $order_id, $start_time);
    send_confirmation_pm($pdo, $user_id, $order_id, $item->title, $price, $quantity);
    return $reply;
}


function unlock_set($pdo, $user_id, $part_ids)
{
    if ($part_ids === 'epic_everything') { // epic_everything
        award_part($pdo, $user_id, 'eHat', '*');
        award_part($pdo, $user_id, 'eHead', '*');
        award_part($pdo, $user_id, 'eBody', '*');
        award_part($pdo, $user_id, 'eFeet', '*');
    } else {
        award_part($pdo, $user_id, 'head', $part_ids[0]);
        award_part($pdo, $user_id, 'body', $part_ids[1]);
        award_part($pdo, $user_id, 'feet', $part_ids[2]);
        award_part($pdo, $user_id, 'eHead', $part_ids[0]);
        award_part($pdo, $user_id, 'eBody', $part_ids[1]);
        award_part($pdo, $user_id, 'eFeet', $part_ids[2]);
    }
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

        if (!$existing_server) { // server doesn't exist in the db
            global $SERVER_IP;

            // insert and start server
            $server_id = server_insert($pdo, $life_from_now, $server_name, $SERVER_IP, $port, $guild_id);
            start_server(PR2_ROOT . '/pr2.php', $port, $server_id, false, true);

            // return data
            $ret->new_time = $life_from_now;
            $ret->status_code = 1;
        } else { // server exists and is either active or inactive
            // get server info
            $server_id = (int) $existing_server->server_id;
            $active = (bool) (int) $existing_server->active;

            // do expiration time calculations
            $life_from_expiry = $existing_server->expire_time + $life_secs;
            $life_from_expiry = $life_from_expiry < $life_from_now ? $life_from_now : $life_from_expiry;

            // update info (and activate server if applicable)
            server_update_expire_time($pdo, $life_from_expiry, $server_id);
            if (!$active) { // if it wasn't active, start the server
                start_server(PR2_ROOT . '/pr2.php', $port, $server_id, false, true);
            }

            // return data
            $ret->new_time = $life_from_expiry;
            $ret->status_code = $active ? 2 : 1; // if server was inactive, return the new server message to user
        }
    } catch (Exception $e) {
        unset($e);
    } finally {
        return $ret;
    }
}


function send_confirmation_pm($pdo, $user_id, $order_id, $title, $price, $quantity)
{
    $cam_link = urlify('https://jiggmin2.com/cam', 'Contact a Mod forum');
    $jv_link = urlify('https://jiggmin2.com/forums', 'Jiggmin\'s Village');
    $pm = 'Howdy! This PM is to confirm your recent Vault of Magics order.'
        ."\n\nOrder ID: $order_id"
        ."\nItem: $title"
        ."\nQuantity: $quantity"
        ."\nCoins Spent: $price"
        ."\n\nThis is an automatically generated PM, so please don't reply. "
        ."If you encounter any problems with your order, please contact us using the $cam_link on $jv_link."
        ."\n\nThanks for your support!\n\n- Jiggmin";
    message_insert($pdo, $user_id, 1, $pm, '0');
}


// regenerates vault items (intended to be run from CLI when there are new changes to vault items)
function regenerate_vault_items($pdo)
{
    require_once QUERIES_DIR . '/vault_items.php';
    output('Regenerating vault items...');

    // select items
    $items = vault_items_select($pdo);

    // populate
    $items_out = new stdClass();
    $items_out->info = new stdClass();
    $items_out->listings = new stdClass();
    foreach ($items as $item) {
        $items_out->listings->{$item->slug} = format_vault_item($item);
    }
    $items_out->info->updated = time();

    // save to file
    $file_link = CACHE_DIR . '/vault.json';
    file_put_contents($file_link, json_encode($items_out, JSON_PRETTY_PRINT));
    output("Vault items regenerated and saved to $file_link.\n");
}
