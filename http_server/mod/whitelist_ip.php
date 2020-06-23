<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/mod_actions.php';

$ip = get_ip();
$action = default_post('action', 'form');
$token = default_post('token');
$header = false;

try {
    // rate limiting
    rate_limit('whitelist-ip-'.$ip, 30, 5);
    rate_limit('whitelist-ip-'.$ip, 3, 1);

    // connect
    $pdo = pdo_connect();

    // check permission
    $mod = check_moderator($pdo);

    // exclude trial mods
    if ($mod->trial_mod) {
        throw new Exception("You lack the power to access this resource.");
    }

    if ($action === 'form') {
        $header = true;
        output_header('Whitelist IP Address', $mod->power >= 2, (int) $mod->power === 3);
        echo '<form method="post">'
            .'IP: <input type="text" name="ip_address" />'
            .'<input type="hidden" name="action" value="do">'
            .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
            .'<br>Are you sure you want to whitelist this IP address?<br>'
            .'<input type="submit" value="Yes, I\'m sure.">&nbsp;(no confirmation!)'
            .'</form>';
    } elseif ($action === 'do') {
        // make sure the token exists and is valid for this mod
        $auth = token_select($pdo, $token);
        if ((int) $auth->user_id !== (int) $mod->user_id) {
            throw new Exception('Could not validate token.');
        }

        // validate IP address
        $wip = default_post('ip_address', '');
        if (!filter_var($wip, FILTER_VALIDATE_IP)) {
            throw new Exception('Invalid IP address specified.');
        }

        // whitelist IP
        $result = whitelist_ip($wip);
        if (!$result) {
            throw new Exception('Could not whitelist IP address.');
        }

        // redirect
        $url = "/mod/ip_info.php?ip=$wip";
        header("Refresh: 2; URL=$url");

        // record action
        $mod_id = (int) $mod->user_id;
        mod_action_insert($pdo, $mod_id, "$mod->name whitelisted IP $wip from $ip.", 'whitelist-ip', $ip);

        // tell the world
        $header = true;
        output_header('Whitelist IP Address', $mod->power >= 2, (int) $mod->power === 3);
        echo 'The operation was successful. Redirecting...'
            ."<br><br><a href='$url'>(click here if you're not automatically redirected)</a>";
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error', $mod->power >= 2, (int) $mod->power === 3);
    }
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
