<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/best_levels/best_levels_monthly.php';
require_once __DIR__ . '/../queries/messages/messages_delete_old.php';
require_once __DIR__ . '/../queries/misc/all_optimize.php';

$pdo = pdo_connect();

best_levels_monthly($pdo);
messages_delete_old($pdo);
all_optimize($pdo);

output('result=ok');
