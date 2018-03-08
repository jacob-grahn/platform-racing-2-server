<?php

header("Content-type: text/plain");
require_once __DIR__ . '/../fns/all_fns.php';

$mode = default_val($_POST['mode'], 'user');
$search_str = default_val($_POST['search_str'], '');
$order = default_val($_POST['order'], '');
$dir = default_val($_POST['dir'], 'desc');
$page = default_val($_POST['page'], 1);
$ip = get_ip();

$page = min(25, $page);
$key = "search-$mode-$search_str-$order-$dir-$page";
$cache_expire = 600; //10 minutes

try {
    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $page_str = apcu_fetch($key);

    while ($page_str === 'WAIT') {
        sleep(1);
        $page_str = apcu_fetch($key);
    }

    if ($page_str === false) {
        rate_limit("$ip-search", 10, 5);
        apcu_add($key, 'WAIT', 5); // will not overwrite existing
        $page_str = search_levels($mode, $search_str, $order, $dir, $page);
        apcu_store($key, $page_str, $cache_expire); // will overwrite existing
    }

    echo $page_str;
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
