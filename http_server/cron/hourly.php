<?php

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();

generate_level_list($db, 'newest');
generate_level_list($db, 'best');
generate_level_list($db, 'best_today');
generate_level_list($db, 'campaign');

echo 'result=ok';

?>
