<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/users/user_select_by_name.php';
require_once __DIR__ . '/../../queries/pr2/pr2_select.php';
require_once __DIR__ . '/../../queries/epic_upgrades/epic_upgrades_select.php';
require_once __DIR__ . '/../../queries/changing_emails/changing_emails_select_by_user.php';
require_once __DIR__ . '/../../queries/recent_logins/recent_logins_select.php';

$name1 = find('name1', '');
$name2 = find('name2', '');
$name3 = find('name3', '');
$name4 = find('name4', '');
$name5 = find('name5', '');
$name6 = find('name6', '');
$name7 = find('name7', '');
$name8 = find('name8', '');
$name9 = find('name9', '');

try {
    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $mod = check_moderator($pdo, false, 3);

    // header
    output_header('Player Deep Info', true, true);

    //
    echo '<form name="input" action="" method="get">';
    foreach (range(1, 9) as $i) {
        $name = ${"name$i"};
        echo '<input type="text" name="name'.$i.'" value="'.htmlspecialchars($name).'"><br>';

        if ($name != '') {
            try {
                $user = user_select_by_name($pdo, $name);
                $pr2 = pr2_select($pdo, $user->user_id);
                $pr2_epic = epic_upgrades_select($pdo, $user->user_id, true);
                $changing_emails = changing_emails_select_by_user($pdo, $user->user_id);
                $logins = recent_logins_select($pdo, $user->user_id);
                echo "user_id: $user->user_id <br/>";
                output_object($user);
                output_object($pr2);
                output_object($pr2_epic);
                output_objects($changing_emails);
                output_objects($logins);
                echo '<a href="update_account.php?id='.$user->user_id.'">edit</a><br><br><br>';
            } catch (Exception $e) {
                echo "<i>Error: ".$e->getMessage()."</i><br><br>";
            }
        }
    }
    echo '<input type="submit" value="Submit">';
    echo '</form>';

    output_footer();
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage();
    output_footer();
}

function output_objects($objs)
{
    foreach ($objs as $obj) {
        output_object($obj, ', ');
        echo '<br/>';
    }
}

function output_object($obj, $sep = '<br/>')
{
    foreach ($obj as $var => $val) {
        if ($var == 'time' || $var == 'register_time') {
            $val = date('M j, Y g:i A', $val);
        }
        if ($var != 'user_id') {
            echo "$var: ".htmlspecialchars($val)."$sep";
        }
    }
}
