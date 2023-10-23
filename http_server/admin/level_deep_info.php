<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/level_deep_info_fns.php';
require_once QUERIES_DIR . '/level_backups.php';

$ip = get_ip();
$level_id = (int) default_get('level_id', 0);

try {
    // rate limiting
    rate_limit('level-deep-info-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('level-deep-info-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    is_staff($pdo, token_login($pdo), false, true, 3);

    // make sure "0" doesn't show up in the box
    $level_id = $level_id === 0 ? '' : $level_id;

    output_header('Level Deep Info', true, true);

    echo '<form name="input" action="" method="get">';
    echo "Level ID: <input type='text' name='level_id' value='$level_id'>&nbsp;";
    echo '<input type="submit" value="Submit"><br>';
    if (!is_empty($level_id, false)) {
        try {
            $level = level_select($pdo, $level_id);
            $level->author = id_to_name($pdo, $level->user_id, true);
            $level_backups = level_backups_select_by_level($pdo, $level_id);
            output_object($level);
            output_objects($level_backups);
            $level_id = (int) $level->level_id;
            echo "<a href='update_level.php?id=$level_id'>edit</a><br><br><br>";
        } catch (Exception $e) {
            $error = $e->getMessage();
            echo "<i>Error: $error</i><br><br>";
        }
    }

    echo '</form>';
} catch (Exception $e) {
    output_error_page($e->getMessage());
} finally {
    output_footer();
}
