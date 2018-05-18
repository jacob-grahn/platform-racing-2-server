<?php

require_once HTTP_FNS . '/output_fns.php';

output_header('Platform Racing 2');

echo '<div class="game_holder">'
        .'<embed width="550" height="400" '
        .'src="//cdn.jiggmin.com/games/platform-racing-2/platform-racing-2-loader-v13.swf" '
        .'type="application/x-shockwave-flash"></embed>'
    .'</div>';

output_footer();
die();
