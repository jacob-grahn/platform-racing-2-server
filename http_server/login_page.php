<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/ip_api_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/ip_validity.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/recent_logins.php';

$action = default_post('action', 'form');
$name = default_post('name', '');
$pass = default_post('pass', '');
$remember = default_post('remember');

$ip = get_ip();

try {
    // rate limit
    rate_limit('login-page-'.$ip, 10, 3);

    // connect
    $pdo = pdo_connect();

    // check if already logged in
    $user_id = token_login($pdo, true, true);
    if ($user_id !== false) {
        rate_limit('login-page-'.$user_id, 15, 1);
        throw new Exception('You are already logged in.');
    }

    // sanity checks
    $action = !in_array($action, ['form', 'login']) ? 'form' : $action;
    if ($action === 'login' && (is_empty($name) || is_empty($pass))) {
        throw new Exception('Some data is missing.');
    }

    if ($action === 'form') {
        output_header('Log In');
        ?>

        <div>Please log into your PR2 account to continue.</div>
        <br />
        <form method="post">
            <table class="noborder">
                <tr>
                    <td class="noborder" style="text-align: right"><label for="name">Name:</label></td>
                    <td class="noborder"><input type="text" name="name" id="name" /></td>
                </tr>
                <tr>
                    <td class="noborder" style="text-align: right"><label for="pass">Pass:</label></td>
                    <td class="noborder"><input type="password" name="pass" id="pass" /></td>
                </tr>
                <tr>
                    <td class="noborder"></td>
                    <td class="noborder"><label><input type="checkbox" name="remember"> Remember Me</label></td>
                </tr>
                <tr>
                    <td class="noborder" colspan="2" style="text-align: center; padding-top: 12.5px">
                        <input type="hidden" name="action" value="login" />
                        <input type="submit" value="Log In" />
                        <input type="reset" />
                    </td>
                </tr>
            </table>
            <br>
            <div><i>NEVER give your password to ANYONE.</i></div>
        </form>

        <?php
    } elseif ($action === 'login') {
        // pass login
        $user = pass_login($pdo, $name, $pass, 'g');
        $user_id = (int) $user->user_id;
        unset($pass);

        // sanity checks
        if ($user->power == 0) { // is a guest?
            throw new Exception('You can\'t log into a Guest account from here.');
        } elseif (strtolower($name) !== strtolower($user->name)) { // names don't match?
            $msg = 'The names don\'t match. If this error persists, contact a member of the PR2 Staff Team.';
            throw new Exception($msg);
        } elseif (strlen(trim($name)) < 2) { // too short?
            throw new Exception('Your name must be at least 2 characters long.');
        } elseif (strlen(trim($name)) > 20) { // too long?
            throw new Exception('Your name cannot be more than 20 characters long.');
        }

        // check IP validity
        $country_code = '?';
        if (!check_ip_validity($pdo, $ip, $user)) {
            $cam_link = urlify('https://jiggmin2.com/cam', 'Contact a Mod');
            $msg = 'Please disable your proxy/VPN to connect to PR2. '.
                "If you feel this is a mistake, please use $cam_link to contact a member of the PR2 staff team.";
            throw new Exception($msg);
        }
        ensure_ip_country_from_valid_existing($pdo, $ip); // if possible, ensure country code isn't ?

        // generate a login token for future requests
        $token = random_str(32);
        token_insert($pdo, $user->user_id, $token);
        if (!empty($remember)) {
            $token_expire = time() + 2592000; // one month
            setcookie('token', $token, $token_expire, '/', $_SERVER['SERVER_NAME'], false, true);
        } else {
            setcookie('token', '', time() - 3600, '/', $_SERVER['SERVER_NAME'], false, true);
        }

        // record moderator login
        if ($user->power > 1 || in_array($user_id, $special_ids)) {
            mod_action_insert($pdo, $user_id, "$name logged in via login_page.php from $ip", 'login_page', $ip);
        }

        // update/record data
        user_update_ip($pdo, $user_id, $ip); // last IP address
        recent_logins_insert($pdo, $user_id, $ip, $country_code); // record recent login

        // show page
        output_header('Log In', $user->power >= 2, $user->power == 3);
        $html_name = htmlspecialchars($user->name, ENT_QUOTES);
        echo "Welcome, $html_name!";
    }
} catch (Exception $e) {
    output_error_page($e->getMessage(), @$user, 'Log In');
} finally {
    output_footer();
}
