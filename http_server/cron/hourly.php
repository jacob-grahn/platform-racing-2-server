<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_select_list.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_delete_old.php';
require_once __DIR__ . '/../queries/part_awards/ensure_awards.php';

$pdo = pdo_connect();

generate_level_list($pdo, 'newest');
generate_level_list($pdo, 'best');
generate_level_list($pdo, 'best_today');
generate_level_list($pdo, 'campaign');
ensure_awards($pdo);

echo 'result=ok';
