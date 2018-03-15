<?php

require_once __DIR__ . '/../fns/all_fns.php';

$pdo = pdo_connect();

generate_level_list($pdo, 'newest');
generate_level_list($pdo, 'best');
generate_level_list($pdo, 'best_today');
generate_level_list($pdo, 'campaign');

echo 'result=ok';
