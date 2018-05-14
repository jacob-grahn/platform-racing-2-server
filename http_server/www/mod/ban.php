<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/users/user_select.php';

$user_id = find_no_cookie('user_id', 0);
$force_ip = find_no_cookie('force_ip');
$reason = find_no_cookie('reason');
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
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error');
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // output header w/ mod nav
    output_header('Ban User', true);

    // get the user's name
    $row = user_select($pdo, $user_id);
    $name = $row->name;
    $target_ip = $row->ip;

    if (isset($force_ip) && $force_ip != '') {
        $target_ip = $force_ip;
    }

    echo "<p>Ban $name [$target_ip]</p>";

    echo '<form action="/ban_user.php" method="post">'
            .'<input type="hidden" value="yes" name="using_mod_site" />'
            .'<input type="hidden" value="yes" name="redirect" />'
            ."<input type='hidden' value='$target_ip' name='force_ip' />"
            ."<input type='hidden' value='$name' name='banned_name' />"
            ."<input type='text' value='$reason' name='reason' size='70' />"
            .'<select name="duration">'
                .'<option value="60">1 Minute</option>'
                .'<option value="3600">1 Hour</option>'
                .'<option value="86400">1 Day</option>'
                .'<option value="604800">1 Week</option>'
                .'<option value="2592000">1 Month</option>'
                .'<option value="31536000">1 Year</option>'
            .'</select>'
            .'<select name="type">'
                .'<option value="account">account</option>'
                .'<option value="ip">ip</option>'
                .'<option value="both" selected="selected">ip and account</option>'
            .'</select>'
            .'<input type="submit" value="Submit" />'
        .'</form>';
} catch (Exception $e) {
    output_header("Ban User", true);
    echo 'Error: '.$e->getMessage();
} finally {
    output_footer();
    die();
}
