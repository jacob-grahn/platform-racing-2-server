<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/ip_api_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/mod_actions.php';

$ip = get_ip();
$mode = default_post('mode', 'form');
$action = default_post('action', '');
$token = default_post('token');
$header = false;

try {
    // rate limiting
    //rate_limit('manage-ip-validity-'.$ip, 3, 1);

    // connect
    $pdo = pdo_connect();

    // check permission
    $mod = check_moderator($pdo, null, false);
    $mod->admin = $mod->power == 3;

    // exclude trial mods
    if ($mod->trial_mod) {
        throw new Exception("You lack the power to access this resource.");
    }

    // output header
    $header = true;
    output_header('IP Validity Management', true, $mod->admin);

    if ($mode === 'form') {
        $admin_opts = '<br><label><input type="radio" name="action" value="block" /> Block IP</label>'
            .'<br><label><input type="radio" name="action" value="clear" /> Clear IP (delete entry)</label>';

        // specific IP form
        echo '<form method="post" id="main">'
            .'IP: <input type="text" name="ip_address" /><br>'
            .'<br>Choose an action:'
            .'<br><label><input type="radio" name="action" value="check" /> Check Validity (no change)</label>'
            .'<br><label><input type="radio" name="action" value="allow" /> Allow IP</label>'
            .($mod->admin ? $admin_opts : '')
            .'<input type="hidden" name="mode" value="one_ip">'
            .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
            .'<br><br><input type="submit" value="Submit">'
            .'</form>';

        // 007: for bls' eyes only
        if ($mod->user_id == 3483035) {
            echo '<br><hr><br><b>Management</b><br><br>';
            echo '<form method="post" id="management">'
                .'Choose an action:'
                .'<br><label><input type="radio" name="action" value="view" /> View status of all stored IPs</label>'
                .'<br><label><input type="radio" name="action" value="clear" /> Clear all invalid stored IPs</label>'
                .'<input type="hidden" name="mode" value="all_ips">'
                .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
                .'<br><br><input type="submit" value="Submit">'
                .'</form>';
        }
    } elseif ($mode === 'one_ip') {
        // referrer check
        require_trusted_ref('', true);

        // make sure the token exists and is valid for this mod
        $auth = token_select($pdo, $token);
        if ((int) $auth->user_id !== (int) $mod->user_id) {
            throw new Exception('Could not validate token.');
        }

        // only let admins block/clear an IP from the system
        if (($action === 'block' || $action === 'clear') && !$mod->admin) {
            throw new Exception('You lack the power to perform this action.');
        }

        // check for valid IP address
        $target_ip = default_post('ip_address', '');
        if (!filter_var($target_ip, FILTER_VALIDATE_IP)) {
            throw new Exception('Invalid IP address specified.');
        }
        $safe_ip = htmlspecialchars($target_ip, ENT_QUOTES); // shouldn't ever be needed. just in case

        // override the IP API's existing entry (or create a new one) for this IP address
        if ($action !== 'check') {
            $result = false;
            if ($action === 'allow' || $action === 'block') {
                $result = override_ip_api($target_ip, $action);
            } elseif ($action === 'clear') {
                $result = apcu_delete("ip-validity-$target_ip");
            }

            // did it succeed?
            if (!$result) {
                throw new Exception("Could not $action the IP address \"$safe_ip\".");
            }

            // record action
            $fn = ($action === 'allow' ? 'mod_' : 'admin_') . 'action_insert';
            $fn($pdo, $mod->user_id, "$mod->name ${action}ed IP $target_ip from $ip.", 'manage-ip-validity', $ip);

            // tell the world
            echo "Successfully {$action}ed the IP address \"$safe_ip\".";
        } else {
            // get entry
            $entry = apcu_exists("ip-validity-$target_ip") ? apcu_fetch("ip-validity-$target_ip") : null;
            $text = isset($entry) ? ucfirst(strtolower($entry)) : 'No record for this IP address.';
            $color = isset($entry) ? ($text === 'Valid' ? 'green' : 'red') : '#c2b613';

            // send back info
            echo "Information for <b>$safe_ip</b><br>"
                ."<br>Status: <span style='color: $color; font-weight: bold'>$text</span>";
        }

        // friendly nav
        echo "<br><br><a href='ip_info?ip=$safe_ip'><- See More Info</a><br>"
            .'<a href="manage_ip_validity.php"><- Process Another IP</a>';
    } elseif ($mode === 'all_ips') {
        // referrer check
        require_trusted_ref('', true);

        // make sure the token exists and is valid for this mod
        $auth = token_select($pdo, $token);
        if ((int) $auth->user_id !== (int) $mod->user_id) {
            throw new Exception('Could not validate token.');
        }

        // sanity: bls under correct circumstances?
        if ($mod->user_id != 3483035 || ($action === 'clear' && strpos($ip, BLS_IP_PREFIX) !== 0)) {
            throw new Exception('You lack the power to perform this action.');
        }

        // perform actions
        if ($action === 'view') {
            echo '<b>Validity of All Cached IPs</b><br><br>';

            // populate IPs from cache
            $full_cache = apcu_cache_info()['cache_list'];
            $valid_ips = $invalid_ips = array();
            foreach ($full_cache as $item) {
                if (strpos($item['info'], 'ip-validity-') !== 0 || $item['creation_time'] + $item['ttl'] <= time()) {
                    continue;
                }

                // make an object from the data
                $r = new stdClass();
                $r->key = $item['info'];
                $r->ip = explode('-', $item['info'])[2];
                $r->expires = format_duration(time() - $item['creation_time'] + $item['ttl']);
                ${strtolower(apcu_fetch($item['info'])) . '_ips'}[] = $r;
            }

            // handle valid IPs
            echo '<span style="color: green"><b><u>Valid IP Addresses</u></b></span><br><br>';
            $count = count($valid_ips);
            if ($count > 0) {
                foreach ($valid_ips as $entry) {
                    echo "<span title='Expires in $entry->expires'>$entry->ip</span><br>";
                }
                echo "<i><b>Total: $count</b></i><br>";
            } else {
                echo '<i>No valid IP addresses logged.</i><br>';
            }

            // handle invalid IPs
            echo '<br><span style="color: red"><b><u>Invalid IP Addresses</u></b></span><br><br>';
            $count = count($invalid_ips);
            if ($count > 0) {
                foreach ($invalid_ips as $entry) {
                    echo "<span title='Expires in $entry->expires'>$entry->ip</span><br>";
                }
                echo "<i><b>Total: $count</b></i><br>";
            } else {
                echo '<i>No invalid IP addresses logged.</i><br>';
            }
            echo "<br><a href='javascript:history.back()'><- Go Back</a>";
        } elseif ($action === 'clear') {
            // populate IPs from cache
            $full_cache = apcu_cache_info()['cache_list'];
            $cleared = 0;
            foreach ($full_cache as $item) {
                if (strpos($item['info'], 'ip-validity-') !== 0 || $item['creation_time'] + $item['ttl'] <= time()) {
                    continue;
                }

                // get validity
                $entry_ip = explode('-', $item['info'])[2];
                $valid = apcu_fetch("ip-validity-$entry_ip") === 'VALID';

                // if invalid, clear from cache
                if (!$valid) {
                    apcu_delete($item['info']);
                    $cleared++;
                }
            }

            // if data was cleared
            if (count($cleared) > 0) {
                $msg = "$mod->name cleared ALL INVALID IP ENTRIES in the server cache from $ip.";
                admin_action_insert($pdo, $mod->user_id, $msg, 'manage-ip-validity', $ip);
    
                echo "Successfully cleared all $cleared invalid IP address entries."
                    .'<br><br><a href="javascript:history.back()"><- Go Back</a>';
            } else {
                throw new Exception('No invalid IP addresses logged to clear.');
            }
        }
    }
} catch (Exception $e) {
    $power = isset($mod) ? $mod->power : 0;
    if ($header === false) {
        output_header('Error', $power >= 2, (int) $power === 3);
    }
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
