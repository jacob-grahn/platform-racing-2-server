<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

$ip = get_ip();

try {
    // verify origin
    require_trusted_ref('', true);

    // connect
    $pdo = pdo_connect();

    // if not bls1999, deny access
    if (token_login($pdo) !== 3483035 || strpos($ip, $BLS_IP_PREFIX) === false) {
        throw new Exception('You lack the power to access this resource.');
    }

    // show the log
    output_header('View Error Log', true, true);
    print_r('<pre>' . file_get_contents('/var/log/nginx/error.log') . '</pre>');
} catch (Exception $e) {
    output_header('Error');
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
