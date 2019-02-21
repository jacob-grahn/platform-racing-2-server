<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

try {
    // verify origin
    require_trusted_ref('', true);

    // connect
    $pdo = pdo_connect();

    if (token_login($pdo) !== 3483035) {
        throw new Exception('You lack the power to access this resource.');
    } else {
        phpinfo();
    }
} catch (Exception $e) {
    output_header('Error');
    $error = $e->getMessage();
    echo "Error: $error";
    output_footer();
}
