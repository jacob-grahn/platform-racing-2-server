<?php

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();

$db->call('servers_deactivate_expired');
$db->call('servers_clean');

echo 'result=ok';

?>
