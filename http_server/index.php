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
echo '<p style="text-align: center;">Adobe Flash Player\'s End-Of-Life date is December 31. '
    .'After this date, PR2 will no longer be accessible in your web browser. '
    .'For information on what this means and how to continue playing PR2 past that date, '
    ."you can read our $info_link on JV.</p>";

output_footer();
