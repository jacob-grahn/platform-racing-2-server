<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

output_header('Platform Racing 2');

// AFP EOL message
$dl_link = urlify('https://pr2hub.com/download', 'Download PR2');
$disc_link = urlify('https://jiggmin2.com/discord', 'Jiggmin\'s Village Discord server');
$eol_msg = '<b>PR2 Lives On!</b><br />'
    ."<br />$dl_link to keep the party going."
    ."<br />Join the $disc_link to stay updated on the latest community developments.";

echo '<div class="game_holder">'
        .'<embed width="550" height="400" '
        .'src="files/platform-racing-2-loader-v14.swf" '
        .'type="application/x-shockwave-flash"></embed>'
        ."<p>$eol_msg</p>"
    .'</div>';

output_footer();
