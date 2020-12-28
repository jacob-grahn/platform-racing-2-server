<?php


// make data to send to the client
function make_listing($desc)
{
    $obj = new stdClass();
    $obj->slug = $desc->slug;
    $obj->title = $desc->title;
    $obj->imgUrl = $desc->imgUrl;
    $obj->price = $desc->price;
    $obj->description = $desc->description;
    $obj->longDescription = $desc->faq;
    $obj->available = $desc->available;
    if (isset($desc->discount)) {
        $obj->discount = $desc->discount;
    }
    return $obj;
}


// decrypt response from Kongregate
function parse_signed_request($signed_request, $secret)
{
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);

    // decode the data
    $sig = base64_url_decode($encoded_sig);
    $data = json_decode(base64_url_decode($payload));

    if (strtoupper($data->algorithm) !== 'HMAC-SHA256') {
        throw new Exception('Unknown algorithm. Expected HMAC-SHA256');
    }

    // check sig
    $expected_sig = hash_hmac('sha256', $payload, $secret, true);
    if ($sig !== $expected_sig) {
        throw new Exception('Bad Signed JSON signature!');
    }

    return $data;
}

// base64 url decode
function base64_url_decode($input)
{
    return base64_decode(strtr($input, '-_', '+/'));
}


// get already purchased items
function get_owned_items($api_key, $kong_user_id)
{
    $url = 'http://www.kongregate.com/api/user_items.json';
    $get = array(
        'api_key' => $api_key,
        'user_id' => $kong_user_id
    );
    $item_str = curl_get($url, $get);
    $item_result = json_decode($item_str);

    if (!$item_result->success) {
        throw new Exception('Could not retrieve a list of your purchased items.');
    }

    return $item_result->items;
}


// buy item from vault
function use_item($api_key, $game_auth_token, $kong_user_id, $item_id)
{
    $url = 'http://www.kongregate.com/api/use_item.json';
    $post = array(
        'api_key' => $api_key,
        'game_auth_token' => $game_auth_token,
        'user_id' => $kong_user_id,
        'id' => $item_id
    );
    $use_result_str = curl_post($url, $post);
    $use_result = json_decode($use_result_str);

    if (!$use_result->success) {
        throw new Exception('Could not use the item.');
    }

    return $use_result;
}



/**
 * Send a POST requst using cURL
 *
 * @param  string $url     to request
 * @param  array  $post    values to send
 * @param  array  $options for cURL
 * @return string
 */
function curl_post($url, array $post = null, array $options = array())
{
    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if (! $result = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}


/**
 * Send a GET requst using cURL
 *
 * @param  string $url     to request
 * @param  array  $get     values to send
 * @param  array  $options for cURL
 * @return string
 */
function curl_get($url, array $get = null, array $options = array())
{
    $defaults = array(
        CURLOPT_URL => $url. (strpos($url, '?') === false ? '?' : ''). http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if (! $result = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}


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
        throw new Exception('Could not retrieve vault info.');
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
            $rented_tokens = rank_token_rentals_count($pdo, $user->user_id, $user->guild);
            $rt_lang = $rented_tokens > 0 ? 'another' : 'a';
            $item->available = $user->guild > 0 && $rented_tokens < 21;
            $item->price = 50 + (20 * $rented_tokens);
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
