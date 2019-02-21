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
function describeVault($pdo, $user_id, $arr)
{

    // gather user info
    $user = user_select_expanded($pdo, $user_id);
    $server = server_select($pdo, $user->server_id);
    $guild = $user->guild != 0 ? guild_select($pdo, $user->guild) : false;

    // build requested descriptions
    $descriptions = array();
    foreach ($arr as $slug) {
        if ($slug == 'stats-boost') {
            $item = describeSuperBooster($user, $server);
        } elseif ($slug == 'guild-fred') {
            $item = describeFred();
        } elseif ($slug == 'guild-ghost') {
            $item = describeGhost();
        } elseif ($slug == 'guild-artifact') {
            $item = describeArtifact();
        } elseif ($slug == 'happy-hour') {
            $item = describeHappyHour($server);
        } elseif ($slug == 'rank-rental') {
            $item = describeRankRental($pdo, $user);
        } elseif ($slug == 'king-set') {
            $item = describeKing($user);
        } elseif ($slug == 'queen-set') {
            $item = describeQueen($user);
        } elseif ($slug == 'djinn-set') {
            $item = describeDjinn($user);
        } elseif ($slug == 'server-1-day') {
            $item = describePrivateServer1($user, $guild);
        } elseif ($slug == 'server-30-days') {
            $item = describePrivateServer30($user, $guild);
        } elseif ($slug == 'epic-everything') {
            $item = describeEpicEverything($user);
        } else {
            throw new Exception('Unknown item type.');
        }

        //$item->price = round($item->price * 0.25);
        $descriptions[] = $item;
    }

    // tell the world
    return $descriptions;
}


// super booster
function describeSuperBooster($user, $server)
{
    $d = new stdClass();
    $d->slug = 'stats-boost';
    $d->title = 'Super Booster';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Super-Booster-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Super-Booster-112x63.png';
    $d->price = 0;
    $d->description = 'Boost all of your stats by 10 for one race. One use per day.';
    $d->available = false;
    $d->faq = "<b>Can I use more than one Super Booster per day if I pay for it?</b>\nNope!\n\n";

    if ($server->tournament == 0) {
        $d->available = !apcu_fetch("sb-$user->user_id");
    }

    return $d;
}


// guild de fred
function describeFred()
{
    $d = new stdClass();
    $d->slug = 'guild-fred';
    $d->title = 'Guild de Fred';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Guild-de-Fred-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Guild-de-Fred-40x40.png';
    $d->price = 20;
    $d->description = 'You and your guild get to party as Fred for an hour.';
    $d->available = true;
    $d->faq = "<b>Is the Guild de Fred power-up useful?</b>\n".
        "- Not at all!\n\n".
        "<b>Do I get to run around as a giant cactus?</b>\n".
        "- Yes. Yes you do.\n\n".
        "<b>How does Guild de Fred work?</b>\n".
        "- A Giant Cactus body is temporarily added to your account. ".
        "You can switch between the Giant Cactus body and your other bodies normally.\n\n";

    return $d;
}


// guild de ghost
function describeGhost()
{
    $d = new stdClass();
    $d->slug = 'guild-ghost';
    $d->title = 'Guild de Ghost';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Guild-de-Ghost-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Guild-de-Ghost-40x40.png';
    $d->price = 10;
    $d->description = 'You and your guild gain (very) invisible parts for an hour.';
    $d->available = true;
    $d->faq = "<b>Will this make me feel like a ninja?</b>\n".
        "- You'll be so ninja.\n\n".
        "<b>Is the Guild de Ghost power-up useful?</b>\n".
        "- It may actually be a massive disadvantage!\n\n".
        "<b>How does Guild de Ghost work?</b>\n".
        "- A very invisible head, body, and feet are temporarily added to your ".
        "account. You can switch between these parts and your other parts normally.\n\n";

    return $d;
}


// guild de artifact
function describeArtifact()
{
    $d = new stdClass();
    $d->slug = 'guild-artifact';
    $d->title = 'Guild de Artifact';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Avatar-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Avatar-40x40.png';
    $d->price = 30;
    $d->description = 'You and your guild gain the artifact as a hat for an hour.';
    $d->available = true;
    $d->faq = "<b>Will the artifact give me tons of EXP every race?</b>\n".
        "- Nope!\n\n".
        "<b>Is the Guild de Artifact power-up useful?</b>\n".
        "- It may actually be a massive disadvantage!\n\n".
        "<b>How does Guild de Artifact work?</b>\n".
        "- Fred has harnessed the power of the artifact for your control! ".
        "The artifact will temporarily be added to your account, ".
        "and you'll still be able switch between it and your other hats normally.\n\n";

    return $d;
}


// happy hour
function describeHappyHour($server)
{
    $d = new stdClass();
    $d->slug = 'happy-hour';
    $d->title = 'Happy Hour';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Happy-Hour-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Happy-Hour-40x40.png';
    $d->price = 50;
    $d->description = 'Is there a happy hour right now? Well there should be.';
    $d->available = false;
    $d->faq = "<b>What's a Happy Hour?</b>\n".
        "- During a Happy Hour everyone on this server will receive double experience points, ".
        "and everyone's speed, acceleration, and jumping are increased to 100.\n\n".
        "<b>Can a Happy Hour be used on a private server?</b>\n".
        "- Yup!\n\n";

    if ($server->tournament == 0) {
        $d->available = true;
    }

    return $d;
}


// rank token++
function describeRankRental($pdo, $user)
{
    $rented_tokens = rank_token_rentals_count($pdo, $user->user_id, $user->guild);
    $d = new stdClass();
    $d->slug = 'rank-rental';
    $d->title = 'Rank Token++';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Rank-Token-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Rank-Token-40x40.png';
    $d->price = 50 + (20 * $rented_tokens);
    $d->description = 'You and your guild all gain a rank token for a week.';
    $d->available = true;
    $d->faq = "<b>What's a Rank Token?</b>\n".
        "- You can use rank tokens to increase or decrease your rank at will. ".
        "A rank 40 account with 3 rank tokens could become a rank 43 account, for example.\n\n".
        "<b>Why does the price change?</b>\n".
        "- The price of a new Rank Token++ depends on how many you currently have.\n".
        "  1st: 50 kreds\n".
        "  2nd: 70 kreds\n".
        "  3rd: 90 kreds\n".
        "  etc\n\n".
        "<b>How many tokens can be used at once?</b>\n".
        "- Up to 21 rank tokens can be rented at a time.\n\n";

    return $d;
}


// wise king set
function describeKing($user)
{
    $d = new stdClass();
    $d->slug = 'king-set';
    $d->title = 'Wise King';
    $d->imgUrl = 'https://pr2hub.com/img/vault/King-Set-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/King-Set-40x40.png';
    $d->price = 30;
    $d->description = 'Permanently add the Wise King Set to your account.';
    $d->available = false;
    $d->faq = "<b>Does the Wise King set give me any stat boosts?</b>\n".
        "- Nope!\n\n".
        "<b>Does the Wise King set make me look totally rad?</b>\n".
        "- Totally.\n\n";

    if (array_search(28, explode(',', $user->head_array)) === false) {
        $d->available = true;
    }

    return $d;
}


// wise queen set
function describeQueen($user)
{
    $d = new stdClass();
    $d->slug = 'queen-set';
    $d->title = 'Wise Queen';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Queen-Set-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Queen-Set-40x40.png';
    $d->price = 30;
    $d->description = 'Permanently add the Wise Queen Set to your account.';
    $d->available = false;
    $d->faq = "<b>Does the Wise Queen set give me any stat boosts?</b>\n".
        "- Nope!\n\n".
        "<b>Does the Wise Queen set make me look totally rad?</b>\n".
        "- Totally.\n\n";

    if (array_search(29, explode(',', $user->head_array)) === false) {
        $d->available = true;
    }

    return $d;
}


// frost djinn set
function describeDjinn($user)
{
    $d = new stdClass();
    $d->slug = 'djinn-set';
    $d->title = 'Frost Djinn';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Djinn-Set-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Djinn-Set-40x40.png';
    $d->price = 30;
    $d->description = 'Permanently add the Frost Djinn Set to your account.';
    $d->available = false;
    $d->faq = "<b>Does the Frost Djinn set give me any stat boosts?</b>\n".
        "- Nope!\n\n".
        "<b>Does the Frost Djinn set make me look totally rad?</b>\n".
        "- Totally.\n\n";

    if (array_search(35, explode(',', $user->head_array)) === false) {
        $d->available = true;
    }

    return $d;
}


// private server 1
function describePrivateServer1($user, $guild)
{
    $d = new stdClass();
    $d->slug = 'server-1-day';
    $d->title = 'Private Server 1';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Private-Server-1-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Private-Server-40x40.png';
    $d->price = 20;
    $d->description = 'Create an exclusive server for your guild. Runs for 1 day.';
    $d->available = false;
    $d->faq = "<b>Who can use a private server?</b>\n".
        "- You and members of your guild can use your private server.\n\n".
        "<b>Can moderators enter our private server?</b>\n".
        "- Nope. You are the law. You'll even have a few admin powers.\n\n".
        "<b>Can I make my own campaign?</b>\n".
        "- Not currently.\n\n";

    if ($guild && $guild->owner_id == $user->user_id) {
        $d->available = true;
    } else {
        $d->faq .= "<b>Why can't I create a private server?</b>\n".
            "- This option is for guild owners only!\n\n";
    }

    return $d;
}


// private server 30
function describePrivateServer30($user, $guild)
{
    $d = new stdClass();
    $d->slug = 'server-30-days';
    $d->title = 'Private Server 30';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Private-Server-30-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Private-Server-40x40.png';
    $d->price = 300;
    $d->description = 'Create an exclusive server for your guild. Runs for 30 days.';
    $d->available = false;
    $d->faq = "<b>Who can use a private server?</b>\n".
        "- You and members of your guild can use your private server.\n\n".
        "<b>Can moderators enter our private server?</b>\n".
        "- Nope. You are the law. You'll even have a few admin powers.\n\n".
        "<b>Can I make my own campaign?</b>\n".
        "- Not currently.\n\n";

    if ($guild && $guild->owner_id == $user->user_id) {
        $d->available = true;
    } else {
        $d->faq .= "<b>Why can't I create a private server?</b>\n".
            "- This option is for guild owners only!\n\n";
    }

    return $d;
}


// epic everything
function describeEpicEverything($user)
{
    $d = new stdClass();
    $d->slug = 'epic-everything';
    $d->title = 'Epic Everything';
    $d->imgUrl = 'https://pr2hub.com/img/vault/Guild-de-Ghost-112x63.png';
    $d->imgUrlSmall = 'https://pr2hub.com/img/vault/Guild-de-Ghost-40x40.png';
    $d->price = 110;
    $d->description = 'Unlock all Epic Upgrades.';
    $d->available = false;
    $d->faq = "<b>What is an Epic Upgrade?</b>\n".
        "- It gives you a second editable color on a part you already own!\n\n".
        "<b>Does this include every Epic Upgrade that exists or ever will exist?</b>\n".
        "- Sure does!\n\n".
        "<b>Does this unlock all the parts too?</b>\n".
        "- No, but all parts you win in the future will automatically come with an Epic Upgrade.\n\n".
        "<b>Do Epic Upgrades provide a stat boost?</b>\n".
        "- Nope!\n\n";

    if (array_search('*', explode(',', $user->epic_heads)) === false) {
        $d->available = true;
    }

    return $d;
}
