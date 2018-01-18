<?php

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();

$db->call('users_reset_status');
$db->call('best_levels_populate');
$db->call('messages_delete_old');
$db->call('all_optimize');

echo 'result=ok';

?>
