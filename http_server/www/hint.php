<?php

require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/data_fns.php';

// rate limiting
try {
    $ip = get_ip();
    rate_limit('gui-hint-'$ip, 5, 2);
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    output_footer();
    die();
}

output_header('Artifact Hint');

echo '<center>'
    .'<font face="Gwibble" class="gwibble">-- Artifact Hint --</font>'
    .'<br>'
    .'<img src="img/artifact.png" width="80px" height="70px"></img>'
    .'<br>';

// get data, make variables
$decode = json_decode(file_get_contents("https://pr2hub.com/files/artifact_hint.txt"));
$safe_hint = htmlspecialchars($decode->hint);
$safe_finder = htmlspecialchars($decode->finder_name);

// echo hint
echo "Here's what Fred can remember: $safe_hint"
    .'<br>';

// echo finder if one, not found yet if none
if (is_empty($decode->finder_name)) {
    echo "<i><b>This artifact hasn't been found yet!</b></i>";
} else {
    echo "<i>The first person to find this artifact was <b>$safe_finder</b>!!</i>";
}
    
// get last updated time
$timestamp = (int) (time() - $decode->updated_time);
$formatted_date = date('M j, Y g:i A', $timestamp);
$formatted_time = format_duration($timestamp);

// echo last updated time
echo '<br><br>'
    ."Fred lost this artifact <span title='$formatted_date'>approximately $formatted_time ago</span>.";

// seeya
echo '</center>';
output_footer();
die();
