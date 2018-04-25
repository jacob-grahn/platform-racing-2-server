<?php

require_once __DIR__ . '/../fns/output_fns.php';

output_header('Platform Racing 2');

// warn when leaving
echo "<script>window.onbeforeunload = function()"
    ."{return true;}</script>";

// game
echo '<div class="game_holder">'
    .'<embed '
    .'width="550" '
    .'height="400" '
    .'src="//cdn.jiggmin.com/games/platform-racing-2/platform-racing-2-loader-v13.swf" '
    .'type="application/x-shockwave-flash"'
    .'></embed>'
    .'</div>';

// that's all, folks
output_footer();
die();
