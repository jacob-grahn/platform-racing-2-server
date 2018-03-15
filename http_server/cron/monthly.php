<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/users/users_reset_status.php';
require_once __DIR__ . '/../queries/best_levels/best_levels_populate.php';
require_once __DIR__ . '/../queries/messages/messages_delete_old.php';
require_once __DIR__ . '/../queries/misc/all_optimize.php';

$pdo = pdo_connect();

users_reset_status($pdo);
best_levels_populate($pdo);
messages_delete_old($pdo);
all_optimize($pdo);

echo 'result=ok';
