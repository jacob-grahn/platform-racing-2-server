<?php


/**
 * Makes a string containing a link to the IP API suitable to query.
 *
 * @param string ip The IP address to be sent to the IP API.
 * @param string key The API key to be used.
 *
 * @return string
 */
function make_ip_api_link($ip, $key)
{
    global $IP_API_LINK_PRE, $IP_API_LINK_SUF;
    return $IP_API_LINK_PRE . $key . '/' . $ip . $IP_API_LINK_SUF;
}


/**
 * Queries the IP API to get information on an IP address.
 *
 * @param string ip The IP address to be sent to the IP API.
 *
 * @return boolean
 * @return object
 */
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


/**
 * Checks if an IP address's validity status is stored in the database; if not, queries the IP API to check validity.
 *
 * @param resource pdo The current database instance.
 * @param string ip The IP address to be checked.
 * @param object||null user A user object, or null.
 * @param boolean handle_cc Indicates if checking/changing the value of $country_code in the calling script.
 *
 * @throws Exception if the ip_validity_select query fails.
 * @throws Exception if the ip_validity_upsert query fails.
 * @return boolean
 */
function check_ip_validity($pdo, $ip, $user = null, $handle_cc = true)
{
    global $IP_API_ENABLED, $BLS_IP_PREFIX, $BANNED_IP_PREFIXES;
    $valid = true;

    // special exceptions
    if (!$IP_API_ENABLED // IP API disabled globally?
        || strpos($ip, $BLS_IP_PREFIX) === 0 // bls?
        || (isset($user) && ($user->power != 1 || $user->verified == 1)) // staff/verified?
    ) {
        return $valid;
    }

    // banned IP prefix
    foreach ($BANNED_IP_PREFIXES as $pre) {
        if (strpos($ip, $pre) === 0) {
            $am = urlify('https://jiggmin2.com/aam', 'Ask a Mod');
            $msg = "This IP range has been permanently banned. Please contact a staff member via $am for more details.";
            throw new Exception($msg);
        }
    }

    // query IP API if not logged in the db
    $ip_data = ip_validity_select($pdo, $ip);
    if (empty($ip_data)) {
        $data = query_ip_api($ip);
        if ($data !== false) {
            $data = json_decode($data);
            if ($data->success) {
                $valid = ip_is_valid($data, $handle_cc);
                ip_validity_upsert($pdo, $ip, $valid);
            }
        }
    } else {
        $valid = (bool) (int) $ip_data->valid;
    }

    return $valid;
}


/**
 * Checks if an IP is valid based on proxy/VPN/tor/recent abuse status.
 *
 * @param object data The data returned from the IP API.
 * @param boolean handle_cc Indicates if checking/changing the value of $country_code in the calling script.
 *
 * @return boolean
 */
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


/**
 * Ensures the correct value is used for $country_code and updates existing entries in the db as necessary.
 *
 * @param resource pdo The current database instance.
 * @param string ip The IP address being checked for a stored country.
 *
 * @throws Exception if the recent_login_select_country_from_ip query fails.
 * @throws Exception if the recent_logins_select_missing_country_from_ip query fails.
 * @throws Exception if the recent_logins_update_missing_country query fails.
 * @return void
 */
function ensure_ip_country_from_valid_existing($pdo, $ip)
{
    global $country_code;

    // don't continue if no country code variable
    if (!isset($country_code)) {
        return;
    }

    // if key exists in db, use it (returns ? on no matches)
    if ($country_code === '?') {
        $country_code = recent_login_select_country_from_ip($pdo, $ip);
    }

    // store valid data
    $missing_count = recent_logins_select_count_missing_country_by_ip($pdo, $ip);
    if ($missing_count > 0 && $country_code !== '?' && strlen($country_code) === 2) {
        recent_logins_update_missing_country($pdo, $ip, $country_code); // store in db (where ? for this IP)
    }
}
