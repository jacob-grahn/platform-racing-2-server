<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once QUERIES_DIR . '/mod_actions.php';

$ip = get_ip();
$action = default_post('action', 'warning');
$user_id = (int) default_get('user_id', 0);
$token = default_post('token');

try {
    // rate limiting
    rate_limit('purge-tokens-'.$ip, 30, 5);
    rate_limit('purge-tokens-'.$ip, 3, 1);

    // connect
    $pdo = pdo_connect();

    // check permission
    $mod = check_moderator($pdo);

    // exclude trial mods
    if ($mod->trial_mod) {
        throw new Exception("You lack the power to access this resource.");
    }

    // get user info
    $user = user_select($pdo, $user_id);
    $name = htmlspecialchars($user->name, ENT_QUOTES);

    if ($action === 'warning') {
        output_header('Purge Active Tokens', $mod->power >= 2, (int) $mod->power === 3);
        echo "Are you sure you want to purge all active login tokens for $name?<br><br>";
        echo '<form method="post">'
            .'<input type="hidden" name="action" value="purge">'
            .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
            .'<input type="submit" value="Yes, I\'m sure.">&nbsp;(no confirmation!)'
            .'</form>';
    } elseif ($action === 'purge') {
        // make sure the mod isn't spamming this command for other staff members
        if ($mod->power < 3 && $user->power >= 2 && $user->user_id != $mod->user_id) {
            $msg = 'You may only purge a staff member\'s tokens once per hour.';
            rate_limit('purge-staff-tokens-'.$mod->user_id, 3600, 1, $msg);
        }

        // make sure the token exists and is valid for this admin
        $auth = token_select($pdo, $token);
        if ((int) $auth->user_id !== (int) $mod->user_id) {
            throw new Exception('Could not validate token.');
        }

        // log user out if online currently
        try {
            $server_id = (int) $user->server_id;
            if ($server_id !== 0) {
                $serv = server_select($pdo, $server_id);
                $send = new stdClass();
                $send->user_id = $user_id;
                $send->message = 'Your account\'s stored login tokens have been purged, '.
                    'so you\'ll need to log in again. Contact a PR2 staff member for more information.';
                $send = json_encode($send);
                talk_to_server($serv->address, $serv->port, $serv->salt, "disconnect_player`$send", false, false);
            }
        } catch (Exception $e) {
            unset($e);
        }

        // purge user's tokens
        tokens_delete_by_user($pdo, $user_id);

        // redirect
        if ((int) $mod->power === 3) {
            $url = '/admin/player_deep_info.php?name1=' . urlencode($user->name);
        } else {
            $url = "/mod/player_info.php?user_id=$user_id";
        }
        header("Refresh: 2; URL=$url");

        // record action
        $mod_id = (int) $mod->user_id;
        mod_action_insert($pdo, $mod_id, "$mod->name purged $name ($user_id)'s tokens from $ip.", 'purge-tokens', $ip);

        // tell the world
        output_header('Purge Active Tokens', $mod->power >= 2, (int) $mod->power === 3);
        echo 'The operation was successful. Redirecting...'
            ."<br><br><a href='$url'>(click here if you're not automatically redirected)</a>";
    }
} catch (Exception $e) {
    output_header('Error', $mod->power >= 2, (int) $mod->power === 3);
    echo 'Error: ' . $e->getMessage();
} finally {
    output_footer();
}
