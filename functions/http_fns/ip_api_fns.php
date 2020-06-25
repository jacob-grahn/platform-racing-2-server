<?php

// crafts an IP API link to query
function make_ip_api_link($ip, $key)
{
    global $IP_API_LINK_PRE, $IP_API_LINK_SUF;
    return $IP_API_LINK_PRE . $key . '/' . $ip . $IP_API_LINK_SUF;
}


// queries the IP API
function query_ip_api($ip)
{
    global $IP_API_KEY_1, $IP_API_KEY_2;

    // define api key and try to use it
    $key = (int) date('j') % 2 === 0 ? $IP_API_KEY_1 : $IP_API_KEY_2;
    $data = @file_get_contents(make_ip_api_link($ip, $key));
    if ($data !== false && !empty($data->success)) {
        return $data;
    }

    // try to use the other one
    $other_key = (int) date('j') % 2 === 0 ? $IP_API_KEY_2 : $IP_API_KEY_1;
    $data = @file_get_contents(make_ip_api_link($ip, $other_key));

    // return whatever we got regardless of an error
    return $data;
}


// checks the validity of an IP address
function check_ip($ip, $user = null, $handle_cc = true)
{
    // disabled globally?
    global $IP_API_ENABLED, $BLS_IP_PREFIX;
    if (!$IP_API_ENABLED || strpos($ip, $BLS_IP_PREFIX) === 0) {
        return true;
    }

    $validity = 'VALID';
    $key = "ip-validity-$ip";
    $verified = (bool) (int) @$user->verified;
    $power_cond = (isset($user) && $user->power == 1) || !isset($user);
    if ($power_cond && !$verified && !apcu_exists($key)) { // if a member, not verified, and ip isn't cached
        $data = query_ip_api($ip); // get ip info
        if ($data !== false) {
            $data = json_decode($data); // decode return data
            if ($data->success) { // if url query succeeded
                $validity = ip_is_valid($data, $handle_cc) ? 'VALID' : 'INVALID'; // determine validity
                apcu_store($key, $validity, 2678400); // log validity
            }
        }
    } elseif (apcu_exists($key)) {
        $validity = apcu_fetch($key);
    }

    return $validity === 'VALID';
}


function ip_is_valid($data, $handle_cc)
{
    global $IP_API_SCORE_MIN;

    $valid = true;
    if ($data->fraud_score > $IP_API_SCORE_MIN
        || $data->proxy
        || $data->vpn
        || $data->tor
        || $data->recent_abuse
    ) {
        $valid = false;
    }

    // update use country code
    if ($handle_cc) {
        global $country_code;
        $country_code = $data->country_code;
    }

    return $valid;
}


// ensure correct country from existing data
function ensure_ip_country_from_valid_existing($pdo, $ip)
{
    global $country_code;
    $key = "ip-country-$ip";

    // don't continue if no country code variable
    if (!isset($country_code)) {
        return;
    }

    // if key exists in apcu, use that. otherwise, retrieve from db (returns ? on no matches)
    if ($country_code === '?') {
        // if there are entries with a ? for this IP or the country_code is ?, continue
        $country_code = apcu_exists($key) ? apcu_fetch($key) : recent_login_select_country_from_ip($pdo, $ip);
    }

    // store valid data
    if (!apcu_exists($key) && $country_code !== '?' && strlen($country_code) === 2) {
        apcu_store($key, $country_code, 2678400); // store in apcu
        recent_logins_update_missing_country($pdo, $ip, $country_code); // store in db (where ? for this IP)
    }
}


// manually sets an IP's validity
function override_ip_api($ip, $action = 'allow')
{
    return apcu_store("ip-validity-$ip", $action === 'allow' ? 'VALID' : 'INVALID', 2678400);
}
