<?php

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/bans.php';

$ip = get_ip();
$enabled = true;

try {
    // verify origin
    require_trusted_ref('', true);

    // connect
    $pdo = pdo_connect();

    // if not bls1999 or not currently enabled, deny access
    if (token_login($pdo) !== 3483035 || strpos($ip, $BLS_IP_PREFIX) === false) {
        throw new Exception('You lack the permission to view this resource.');
    } elseif ($enabled === false) {
        throw new Exception('This resource is currently disabled.');
    }

    // make the page
    $header = true;
    output_header('Admin DB Operation', true, true);

    // show error info
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // make the query
    $stmt = $pdo->query('SELECT * FROM bans WHERE lifted_reason IS NOT NULL LIMIT 100000;');
    $bans = $stmt->fetchAll(PDO::FETCH_OBJ);

    echo '<pre>';
    $i = 0;
    foreach ($bans as $ban) {
        // separate reason info
        $arr = explode(' @', $ban->lifted_reason);
        $time = strtotime(array_pop($arr));
        $reason = join(' @', $arr);

        // move lifted time to a new column
        $stmt = $pdo->prepare('UPDATE bans SET lifted_reason = :reason, lifted_time = :time WHERE ban_id = :ban_id;');
        $stmt->bindValue(':ban_id', $ban->ban_id, PDO::PARAM_INT);
        $stmt->bindValue(':reason', $reason, PDO::PARAM_STR);
        $stmt->bindValue(':time', $time, PDO::PARAM_INT);
        $result = $stmt->execute();

        // increment
        $reason = htmlspecialchars($reason, ENT_QUOTES);
        echo "$ban->ban_id done -- Reason: $reason | Time: $time";
        $i++;
    }
    echo 'Success! $i rows affected.';
    echo '</pre>';
} catch (Exception $e) {
    echo $e->getMessage();
}
