<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/servers/server_select.php';
require_once QUERIES_DIR . '/staff/actions/mod_action_insert.php';
require_once QUERIES_DIR . '/tokens/tokens_delete_by_user.php';

$ip = get_ip();
$action = find('action', 'warning');
$user_id = (int) find('user_id', 0);
$token = $_POST['token'];
$header = false;

try {
    // rate limiting
    rate_limit('purge-tokens-'.$ip, 30, 5);
    rate_limit('purge-tokens-'.$ip, 3, 1);

    // connect
    $pdo = pdo_connect();

    // check permission
    $mod = check_moderator($pdo);
    $mod->is_mod = true;

    // exclude trial mods
    if ($mod->can_unpublish_level != 1) {
        throw new Exception("You lack the power to access this resource.");
    }

    // get user info
    $user = user_select($pdo, $user_id);
    $name = htmlspecialchars($user->name, ENT_QUOTES);

    if ($action === 'warning') {
        $header = true;
        output_header('Purge Active Tokens', true);
        echo "Are you sure you want to purge all active login tokens for $name?<br><br>";
        echo '<form method="post">'
            .'<input type="hidden" name="action" value="purge">'
            .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
            .'<input type="submit" value="Yes, I\'m sure.">&nbsp;(no confirmation!)'
            .'</form>';
    } elseif ($action === 'purge') {
        // make sure the mod isn't spamming this command for other staff members
        if ($mod->power < 3 && $user->power >= 2 && $user->user_id != $mod->user_id) {
            rate_limit(
                'purge-staff-tokens-'.$mod->user_id,
                3600,
                1,
                'You may only purge a staff member\'s tokens once per hour.'
            );
        }

        // make sure the token exists and is valid for this admin
        $auth = token_select($pdo, $token);
        if ($auth->user_id != $mod->user_id) {
            throw new Exception('Could not validate token.');
        }

        // log user out if online currently
        try {
            $server_id = (int) $user->server_id;
            if ($server_id !== 0) {
                $server = server_select($pdo, $server_id);
                $data = new stdClass();
                $data->user_id = $user_id;
                $data->message = 'Your account\'s stored login tokens have been purged, '.
                    'so you\'ll need to log in again. Contact a PR2 staff member for more information.';
                $data = json_encode($data);
                talk_to_server(
                    $server->address,
                    $server->port,
                    $server->salt,
                    'disconnect_player`' . $data,
                    false,
                    false
                );
            }
        } catch (Exception $e) {
            unset($e);
        }

        // purge user's tokens
        tokens_delete_by_user($pdo, $user_id);

        // redirect
        if ($mod->power == 3) {
            $url = '/admin/player_deep_info.php?name1=' . urlencode($user->name);
        } else {
            $url = "/mod/player_info.php?user_id=$user_id";
        }
        header("Refresh: 2; URL=$url");

        // record action
        $mod_id = $mod->user_id;
        $mod_name = $mod->name;
        mod_action_insert($pdo, $mod_id, "$mod_name purged $name ($user_id)'s tokens from $ip.", $mod_id, $ip);

        // tell the world
        $header = true;
        output_header('Purge Active Tokens', true);
        echo 'The operation was successful. Redirecting...'
            ."<br><br><a href='$url'>(click here if you're not automatically redirected)</a>";
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error', $mod->is_mod);
    }
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
