<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';

$ip = get_ip();
$action = default_post('action', 'form');
$header = false;

try {
    // verify origin
    require_trusted_ref('', true);

    // connect
    $pdo = pdo_connect();

    // if not bls1999, deny access
    if (token_login($pdo) !== 3483035 || strpos($ip, $BLS_IP_PREFIX) === false) {
        throw new Exception('You lack the permission to view this resource.');
    }

    $header = true;
    output_header('Admin DB Query', true, true);

    if ($action === 'form') {
        $token = $_COOKIE['token'];
        echo '<form method="post" id="query_form">';
        echo '<textarea cols="100" rows="10" name="query_box" form="query_form"></textarea>';
        echo "<input type='hidden' name='token' value='$token'>";
        echo '<input type="hidden" name="action" value="do">';
        echo '<br><br><input type="submit" value="Go"> &nbsp;(no confirmation!)';
        echo '</form>';
    } elseif ($action === 'do') {
        $token = default_post('token', '');
        if ((int) token_select($pdo, $token)->user_id !== 3483035) {
            throw new Exception('You lack the permission to view this resource.');
        }

        // give error information
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // make sure nothing sketchy is going on
        $query = trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $_POST['query_box']));
        $lower_query = strtolower($query);
        if (strpos($lower_query, 'pass_hash') !== false
            || strpos($lower_query, 'temp_pass_hash') !== false
            || strpos($lower_query, '* from users') !== false
            || strpos($lower_query, 'from tokens')
            || strpos($lower_query, 'drop') === 0
            || is_empty($query)
        ) {
            throw new Exception('Illegal operation performed.');
        }

        // don't overload the db
        if (strpos($lower_query, 'select') === 0 && strpos($lower_query, 'limit') === false) {
            if (substr($query, -1) === ';') {
                $query = substr($query, 0, strlen($query) - 1);
            }
            $query = $query . ' LIMIT 0,30;';
        }

        // perform the query
        $stmt = $pdo->query("$query");
        $start_time = microtime(true);
        $result = $stmt->execute();
        $end_time = microtime(true);
        if ($result === false) {
            throw new Exception($pdo->errorInfo());
        } else {
            $action_msg = "DB QUERY -- bls1999 (3483035) from $ip: $query";
            admin_action_insert($pdo, 3483035, $action_msg, 3483035, $ip);
        }
        echo 'Query: ' . htmlspecialchars($query, ENT_QUOTES) . '<br><br>';
        echo 'This query took ' . ($end_time - $start_time) . ' seconds to complete.<br><br>';
        echo '<pre>';
        var_dump($stmt->fetchAll(PDO::FETCH_OBJ));
        echo '</pre>';
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error');
    }
    $error = $e->getMessage();
    if ($error === 'Database Error -> ' || strpos($error, 'SQL') === 0) {
        echo 'Database Error: ' . print_r($error, true);
    } else {
        echo "Error: $error";
    }
} finally {
    if ($action !== 'form' || $header === false) {
        echo '<br><br><a href="javascript:history.back()"><- Go Back</a>';
    }
    output_footer();
}
