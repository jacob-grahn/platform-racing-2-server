<?php

require_once HTTP_FNS . '/output_fns.php';

output_header('PR2 Local Level Client');

echo '<div class="game_holder">'
    .'<embed width="550" height="400" src="files/localpr2_levels.swf" type="application/x-shockwave-flash" />'
    .'</div>';

output_footer();
