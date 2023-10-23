<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

$user_id = default_get('user_id', 0);
$force_ip = default_get('force_ip', '');
$reason = htmlspecialchars(default_get('reason', ''), ENT_QUOTES);
$ip = get_ip();

try {
    // sanity check: who are you trying to ban?
    if (is_empty($user_id, false)) {
        throw new Exception("No user specified.");
    }

    // rate limiting
    rate_limit('mod-ban-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);

    // output header
    output_header('Ban User', $mod->power >= 2, (int) $mod->power === 3);

    // check for trial
    if ($mod->trial_mod) {
        throw new Exception('You lack the power to access this resource. Please ban this user from PR2.');
    }

    // get the user's info
    $row = user_select($pdo, $user_id);
    $name = htmlspecialchars($row->name, ENT_QUOTES);
    $target_ip = filter_var($force_ip, FILTER_VALIDATE_IP) ? $force_ip : $row->ip;

    echo "<p>Ban $name [$target_ip]</p>";

    echo '<form action="/ban_user.php" method="post">'
            .'<input type="hidden" value="yes" name="using_mod_site" />'
            .'<input type="hidden" value="yes" name="redirect" />'
            ."<input type='hidden' value='$target_ip' name='force_ip' />"
            ."<input type='hidden' value='$name' name='banned_name' />"
            ."<input type='text' value='$reason' name='reason' size='70' /> "
            .'<select name="duration">'
                .'<option value="3600">1 Hour</option>'
                .'<option value="86400">1 Day</option>'
                .'<option value="259200">3 Days</option>'
                .'<option value="604800">1 Week</option>'
                .'<option value="1209600">2 Weeks</option>'
                .'<option value="2592000">1 Month</option>'
                .'<option value="15768000">6 Months</option>'
                .'<option value="31536000">1 Year</option>'
            .'</select> '
            .'<select name="type">'
                .'<option value="both" selected="selected">Account and IP</option>'
                .'<option value="account">Account Only</option>'
                .'<option value="ip">IP Only</option>'
            .'</select> '
            .'<select name="scope">'
                .'<option value="game">Game</option>'
                .'<option value="social">Social</option>'
            .'</select> '
            .'<input type="submit" value="Submit" />'
        .'</form>';
} catch (Exception $e) {
    output_error_page($e->getMessage(), @$mod);
} finally {
    output_footer();
}
