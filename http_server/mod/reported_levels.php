<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/levels_reported.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);

$next_form_id = 1;
$mod_ip = get_ip();

$reasons = [
    'Vulgar Language',
    'Harassment',
    'Sensitive Imagery',
    'Scamming',
    'Copying (w/o attrib)',
    'Republishing Removed Level'
];

try {
    // rate limiting
    rate_limit('mod-reported-levels-'.$mod_ip, 5, 3);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $staff = is_staff($pdo, token_login($pdo), false, true);

    // get the levels
    $levels = levels_reported_select($pdo, $start, $count);

    // output header
    output_header('Reported Levels', $staff->mod, $staff->admin);

    // navigation
    output_pagination($start, $count);
    echo '<p>---</p>';

    // output the levels
    foreach ($levels as $level) {
        $formatted_time = date('M j, Y g:i A', $level->reported_time);
        $reporter_name = str_replace(' ', '&nbsp;', htmlspecialchars($level->reporter, ENT_QUOTES));
        $rid = (int) $level->reporter_user_id;
        $rip = $level->reporter_ip;
        $creator_name = str_replace(' ', '&nbsp;', htmlspecialchars($level->creator, ENT_QUOTES));
        $cid = (int) $level->creator_user_id;
        $cip = $level->creator_ip;
        $archived = (bool) (int) $level->archived;
        $level_id = (int) $level->level_id;
        $title = htmlspecialchars(filter_swears($level->title), ENT_QUOTES);
        $note = str_replace("\r", '<br>', htmlspecialchars(filter_swears($level->note), ENT_QUOTES));
        $reason = str_replace("\r", '<br>', htmlspecialchars(filter_swears($level->report_reason), ENT_QUOTES));
        $info = "Level Title: $title\nLevel Note: $note\n";
        $class = $archived === true ? 'archived' : 'not-archived';

        echo "<br><div class='$class'><p>".
             "<a href='player_info.php?user_id=$rid&force_ip=$rip'>$reporter_name</a> reported a level by ".
             "<a href='player_info.php?user_id=$cid&force_ip=$cip'>$creator_name</a> on $formatted_time".
             '<p>'.
             "<p><i>Title:</i> $title<br>".
             "<i>Note:</i> $note</p>".
             "<p><i>Reason for report:</i> $reason</p>";

        if ($archived === false) {
            $form_id = 'f'.$next_form_id;
            $button_id = 'b'.$next_form_id;
            $div_id = 'd'.$next_form_id++;
            echo "<div id='$div_id'>"
                ."<input id='$button_id' type='submit' value='Archive'><br>"
                ."-- or --<br>"
                ."<form id='$form_id' action='../ban_user.php' method='post'>"
                    ."<input type='hidden' value='yes' name='using_mod_site'>"
                    ."<input type='hidden' value='$creator_name' name='banned_name'>"
                    ."<input type='hidden' value='$cip' name='force_ip'>"
                    ."<input type='hidden' value='social' name='scope'>"
                    ."<input type='hidden' value='$info' name='record'>";
            foreach ($reasons as $reason) {
                echo "<button type='submit' name='reason' value='Inappropriate Level -- $reason'>$reason</button> ";
            }
            echo ""
                    ."<select name='duration'>"
                        ."<option value='3600' selected='selected'>1 Hour</option>"
                        ."<option value='86400'>1 Day</option>"
                        ."<option value='604800'>1 Week</option>"
                        ."<option value='2592000'>1 Month</option>"
                        ."<option value='31536000'>1 Year</option>"
                    ."</select>"
                ."</form>"
                ."</div>"
                ."<script>
                    $('#$form_id').ajaxForm(function() {
                        $('#$div_id').remove();
                        $.post('archive_report.php', {level_id: $level_id});
                    });
                    $('#$button_id').click(function() {
                        $('#$div_id').remove();
                        $.post('archive_report.php', {level_id: $level_id});
                    });
                </script>";
        }

        echo "</div><p>&nbsp;</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
