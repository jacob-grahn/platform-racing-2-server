<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/servers/server_select.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';
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
    $admin = check_moderator($pdo, false, 3);

    // get user info
    $user = user_select($pdo, $user_id);
    $name = htmlspecialchars($user->name, ENT_QUOTES);

    if ($action === 'warning') {
        $header = true;
        output_header('Purge Active Tokens', true, true);
        echo "Are you sure you want to purge all active login tokens for $name?<br><br>";
        echo '<form method="post">'
            .'<input type="hidden" name="action" value="purge">'
            .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
            .'<input type="submit" value="Yes, I\'m sure.">&nbsp;(no confirmation!)'
            .'</form>';
    } elseif ($action === 'purge') {
        // referrer check
        require_trusted_ref('', true);

        // make sure the token exists and is valid for this admin
        $auth = token_select($pdo, $token);
        if ($auth->user_id != $admin->user_id) {
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
                    'so you\'ll need to log in again. Contact an admin for more information.';
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
        $url = "player_deep_info.php?name1=" . urlencode($user->name);
        header("Refresh: 2; URL=$url");

        // record action
        $admin_id = $admin->user_id;
        $admin_name = $admin->name;
        admin_action_insert($pdo, $admin_id, "$admin_name purged $name ($user_id)'s tokens from $ip.", $admin_id, $ip);

        // tell the world
        $header = true;
        output_header('Purge Active Tokens', true, true);
        echo 'The operation was successful. Redirecting...'
            ."<br><br><a href='$link'>(click here if you're not automatically redirected)</a>";
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error');
    }
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
