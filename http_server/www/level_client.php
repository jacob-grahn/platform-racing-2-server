<?php
require_once __DIR__ . '/../fns/output_fns.php';
output_header('PR2 Local Level Client');
?>

<div class="game_holder">
    <embed width="550"
           height="400"
           src="files/localpr2_levels.swf"
           type="application/x-shockwave-flash">
    </embed>
</div>

<?php
output_footer();
?>
