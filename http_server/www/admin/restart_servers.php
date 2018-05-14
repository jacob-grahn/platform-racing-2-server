<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/servers/servers_select.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

$ip = get_ip();
$action = find('action', 'warning');
$token = $_POST['token'];

try {
    // rate limiting
    rate_limit('restart-servers-'.$ip, 30, 5);
    rate_limit('restart-servers-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();
    
    // check permission
    $admin = check_moderator($pdo, false, 3);
} catch (Exception $e) {
    output_header('Error');
    echo "Error: " . $e->getMessage();
    output_footer();
    die();
}

try {
    // output header
    output_header('Restart Servers', true, true);
    
    if ($action == 'warning') {
        echo "WARNING: Continuing will restart every PR2 server. "
            ."If you choose to proceed, this action will disconnect EVERY player currently online on every server. "
            ."Are you SURE you want to disconnect all players and restart all PR2 servers?<br><br>";
        echo '<form method="post">'
            .'<input type="hidden" name="action" value="restart">'
            .'<input type="hidden" name="token" value="'.$_COOKIE['token'].'">'
            .'<input type="submit" value="Yes, RESTART ALL PR2 SERVERS">&nbsp;(no confirmation!)'
            .'</form>';
        output_footer();
        die();
    } elseif ($action == 'restart') {
        // referrer check
        require_trusted_ref('', true);
        
        // make sure the token exists and is valid for this admin
        $auth = token_select($pdo, $token);
        if ($auth->user_id != $admin->user_id) {
            throw new Exception('Could not validate token.');
        }
    
        // only let the servers be restarted with this method once per hour
        rate_limit(
            'do-restart-servers',
            3600,
            1,
            'Please wait at least one hour before attempting to restart all active servers again.'
        );
        
        // get servers
        $servers = servers_select($pdo);
        
        // test all active servers at this address
        foreach ($servers as $server) {
            echo "Shutting down $server->server_name ($server->server_id)...<br>";
            try {
                $reply = talk_to_server($server->address, $server->port, $server->salt, 'shut_down`', true);
                echo "Server Reply: $reply";
            } catch (Exception $e) {
                echo $e->getMessage();
            } finally {
                echo '<br><br>';
            }
        }

        // record action
        $admin_id = $admin->user_id;
        $admin_name = $admin->name;
        admin_action_insert($pdo, $admin_id, "$admin_name restarted ALL ACTIVE PR2 SERVERS from $ip.", $admin_id, $ip);
        
        // tell the world
        echo '<span style="color: green;">All operations completed.</span>';
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
} finally {
    output_footer();
    die();
}
