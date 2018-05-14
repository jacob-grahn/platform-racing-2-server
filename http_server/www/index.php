<?php

require_once HTTP_FNS . '/output_fns.php';

output_header('Platform Racing 2');

echo "<div class='game_holder'>"
    ."<embed id='game' width='550' height='400' src='files/localpr2.swf' type='application/x-shockwave-flash'></embed>"
    ."</div>";

output_footer();
die();
