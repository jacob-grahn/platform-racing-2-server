<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

output_header('Platform Racing 2');

echo '<div class="game_holder">'
        .'<embed width="550" height="400" '
        .'src="files/platform-racing-2-loader-v14.swf" '
        .'type="application/x-shockwave-flash"></embed>'
    .'</div>';

// AFP EOL message
$info_link = urlify('https://jiggmin2.com/forums/showthread.php?tid=3182', 'response to Flash Player\'s EOL');
$eol_msg = '<b>IMPORTANT: PR2 won\'t be shutting down on January 1.</b>'
    ."<br />Read our $info_link for information on how to play after December 31.";

echo "<p>$eol_msg</p>";

output_footer();
