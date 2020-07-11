<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/messages_reported.php';
require_once QUERIES_DIR . '/levels_reported.php';

$mode = default_get('mode', 'messages');
$mode = $mode !== 'messages' && $mode !== 'levels' ? 'messages' : $mode;
$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);

$next_form_id = 1;
$mod_ip = get_ip();

$levels_reasons = [
    'Vulgar Language',
    'Harassment',
    'Sensitive Imagery',
    'Scamming',
    'Copying (w/o attrib)',
    'Republishing Removed Level'
];

$messages_reasons = [
    'Vulgar Language',
    'Harassment',
    'Sexual Content',
    'Dangerous/Malicious Content',
    'Scamming'
];

$reasons = ${"${mode}_reasons"};

try {
    // rate limiting
    rate_limit('mod-reported-levels-'.$mod_ip, 5, 3);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $staff = is_staff($pdo, token_login($pdo), false, true);

    // output header
    $header = true;
    output_header('Reported ' . ucfirst($mode), $staff->mod, $staff->admin);

    // navigation
    output_pagination($start, $count, "&mode=$mode");
    echo '<p>---</p>';

    // get the reports of this mode
    $fn = "${mode}_reported_select";
    ${$mode} = $fn($pdo, $start, $count);

    // sanity: no reports?
    if (empty(${$mode})) {
        throw new Exception('No reports available for that search criteria.');
    }

    // handle levels
    if ($mode === 'levels') {
        $time = 'reported_time';
        $reporter_name = 'reporter';
        $reporter_uid = 'reporter_user_id';
        $offender_name = 'creator';
        $offender_uid = 'creator_user_id';
        $offender_ip = 'creator_ip';
        $item_id = 'level_id';
        $item_body = 'note';
    } elseif ($mode === 'messages') {
        $time = 'sent_time';
        $reporter_name = 'to_name';
        $reporter_uid = 'to_user_id';
        $offender_name = 'from_name';
        $offender_uid = 'from_user_id';
        $offender_ip = 'from_ip';
        $item_id = 'message_id';
        $item_body = 'message';
    } else {
        throw new Exception('Unable to declare a mode.'); // should never happen
    }

    // output the items according to mode
    foreach (${$mode} as $item) {
        $formatted_time = date('M j, Y g:i A', $item->$time);
        $rname = htmlspecialchars($item->$reporter_name, ENT_QUOTES);
        $disp_rname = str_replace(' ', '&nbsp;', $rname);
        $rid = (int) $item->$reporter_uid;
        $rip = $item->reporter_ip;
        $oname = htmlspecialchars($item->$offender_name, ENT_QUOTES);
        $disp_oname = str_replace(' ', '&nbsp;', $oname);
        $oid = (int) $item->$offender_uid;
        $oip = $item->$offender_ip;
        $archived = (bool) (int) $item->archived;
        $this_id = (int) $item->$item_id;
        $body = htmlspecialchars(filter_swears($item->$item_body), ENT_QUOTES);
        $disp_body = nl2br($body);

        // if level, define some extra vars
        if (!empty($levels)) {
            $title = htmlspecialchars(filter_swears($item->title), ENT_QUOTES);
            $version = (int) $item->version;
            $reason4rep = nl2br(htmlspecialchars(filter_swears($item->report_reason, ENT_QUOTES)));
            $record = "Level ID: $this_id\nTitle: $title\nNote: $body\n\nVersion: $version\n";

            $text = "<a href='player_info.php?user_id=$rid&force_ip=$rip'>$disp_rname</a> reported a level by ".
                "<a href='player_info.php?user_id=$oid&force_ip=$oip'>$disp_oname</a> on $formatted_time".
                '<p>'.
                "<p><i>Level ID:</i> $this_id<br>".
                "<i>Title:</i> $title<br>".
                "<i>Note:</i> $disp_body</p>".
                "<p><i>Version:</i> $version<br>".
                "<i>Reason for report:</i> $reason4rep";
        } else {
            $text = "<a href='player_info.php?user_id=$oid&force_ip=$oip'>$disp_oname</a> sent this message to ".
                "<a href='player_info.php?user_id=$rid&force_ip=$rip'>$disp_rname</a> on $formatted_time".
                "<p><p>$body";
        }

        $class = $archived === true ? 'archived' : 'not-archived';
        echo "<br><div class='$class'><p>$text</p>";

        if ($archived === false) {
            $form_id = 'f'.$next_form_id;
            $button_id = 'b'.$next_form_id;
            $div_id = 'd'.$next_form_id++;
            echo "<div id='$div_id'>"
                ."<input id='$button_id' type='submit' value='Archive'><br>"
                ."-- or --<br>"
                ."<form id='$form_id' action='../ban_user.php' method='post'>"
                    ."<input type='hidden' value='yes' name='using_mod_site'>"
                    ."<input type='hidden' value='$oname' name='banned_name'>"
                    ."<input type='hidden' value='$oip' name='force_ip'>"
                    ."<input type='hidden' value='social' name='scope'>";
            echo !empty($levels) ? "<input type='hidden' value='$record' name='record'>"
                ."<input type='hidden' value='$this_id' name='level_id'>" : '';
            foreach (${"${mode}_reasons"} as $reason) {
                $full_reason = !empty($levels) ? "Inappropriate Level -- $reason" : "$reason in PMs";
                echo "<button type='submit' name='reason' value='$full_reason'>$reason</button> ";
            }
            $send = new stdClass();
            $send->$item_id = $this_id;
            if (!empty($levels)) {
                $send->version = $version;
            }
            $send = json_encode($send);
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
                        $.post('archive_report.php', $send);
                    });
                    $('#$button_id').click(function() {
                        $('#$div_id').remove();
                        $.post('archive_report.php', $send);
                    });
                </script>";
        }

        echo "</div><p>&nbsp;</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count, "&mode=$mode");
} catch (Exception $e) {
    if (!isset($header)) {
        output_header("Error");
    }
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
