<?php

require_once '../fns/output_fns.php';

output_header('Artifact Hint');

echo '<center><font face="Gwibble" class="gwibble">-- Artifact Hint --</font>
	<br/>
	<img src="img/artifact.png" width="80px" height="70px"></img>
	<br/>';
try {
    $decode = json_decode(file_get_contents("https://pr2hub.com/files/artifact_hint.txt"));
    echo "Here's what Fred can remember: " . htmlspecialchars($decode->hint) . "<br />";
    if ($decode->finder_name == "") {
        echo "<i><b><br />This artifact hasn't been found yet!</i>";
    } else {
        echo "<br /> The first person to find this artifact was " . htmlspecialchars($decode->finder_name) . "!!</i>";
    }
} catch (Exception $msg) {
    $error_message = htmlspecialchars("Error: " . $msg->getMessage());
    echo $error_message;
}

echo '</center>';
output_footer();
