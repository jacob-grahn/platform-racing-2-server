<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/player_deep_info_fns.php';
require_once QUERIES_DIR . '/changing_emails.php';
require_once QUERIES_DIR . '/folding_at_home.php';
require_once QUERIES_DIR . '/rank_tokens.php';
require_once QUERIES_DIR . '/recent_logins.php';

$name1 = default_get('name1', '');
$name2 = default_get('name2', '');
$name3 = default_get('name3', '');
$name4 = default_get('name4', '');
$name5 = default_get('name5', '');
$name6 = default_get('name6', '');
$name7 = default_get('name7', '');
$name8 = default_get('name8', '');
$name9 = default_get('name9', '');

try {
    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    is_staff($pdo, token_login($pdo), false, true, 3);

    // header
    output_header('Player Deep Info', true, true);

    // output page
    echo '<form name="input" action="" method="get">';
    foreach (range(1, 9) as $i) {
        $name = ${"name$i"};
        $safe_name = htmlspecialchars($name, ENT_QUOTES);
        echo "<input type='text' name='name$i' value='$safe_name'><br>";

        if ($name !== '') {
            try {
                $user = user_select_by_name($pdo, $name);
                $pr2 = pr2_select($pdo, $user->user_id, true);
                $epic = epic_upgrades_select($pdo, $user->user_id, true);
                $rank_tokens = rank_token_select($pdo, $user->user_id);
                $user->level_count = count(levels_select_by_owner($pdo, $user->user_id));
                $folding = folding_select_by_user_id($pdo, $user->user_id, true);
                $changing_emails = changing_emails_select_by_user($pdo, $user->user_id, true);
                $logins = recent_logins_select($pdo, $user->user_id, true);
                $user_id = (int) $user->user_id;
                echo "user_id: $user_id <br/>";
                output_object($user);
                output_object($pr2);
                output_object($epic);
                output_object($rank_tokens);
                output_object_keys($folding);
                output_objects($changing_emails);
                output_objects($logins, true, $user);
                echo "<a href='update_account.php?id=$user_id'>edit</a>"
                    ." | <a href='/mod/ban.php?user_id=$user_id&force_ip='>ban</a>"
                    ." | <a href='/mod/purge_tokens.php?user_id=$user_id'>purge tokens</a>"
                    .'<br><br><br>';
            } catch (Exception $e) {
                $error = $e->getMessage();
                echo "<i>Error: $error</i><br><br>";
            }
        }
    }
    echo '<input type="submit" value="Submit">';
    echo '</form><br>';
    echo 'If you don\'t have a player\'s name, try <a href="find_player.php">searching for them</a>.';
} catch (Exception $e) {
    output_header('Error');
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
