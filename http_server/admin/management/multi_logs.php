<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/servers.php';

$ip = get_ip();
$file = default_get('file', '');
$header = false;

try {
    // verify origin
    require_trusted_ref('', true);

    // connect
    $pdo = pdo_connect();

    if (token_login($pdo) !== 3483035 || strpos($ip, $BLS_IP_PREFIX) === false) {
        throw new Exception('You lack the power to access this resource.');
    }

    // header
    $header = true;
    output_header('Debug PR2 Servers', true, true);

    // no file specified
    if (is_empty($file)) {
        // make sure this completes
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        // get directory contents
        $contents = array_diff(scandir(ROOT_DIR . '/../pr2/log'), array('..', '.'));

        // organize files by port (server)
        $ports = array();
        foreach ($contents as $path) {
            // parse file name
            $arr = explode('-', $path);
            $port = (int) $arr[0];
            $day_pos = strcspn($arr[1], '0123456789');
            $date = substr($arr[1], 0, $day_pos) . ' ' . substr($arr[1], $day_pos) . ', ' . $arr[2];
            $time = explode('.', $arr[3])[0];

            // make port array if non-existent
            if (empty(${$port . '_arr'})) {
                ${$port . '_arr'} = array();
                array_push($ports, $port);
            }

            // organize file info
            $timestamp = strtotime($arr[1] . ' ' . $arr[2] . ' ' . $time);
            $disp_date = date('F j, Y \a\t g:ia', $timestamp);
            $line = "<a href='multi_logs.php?file=$path'>$disp_date</a>";

            // push to port array as object
            $obj = new stdClass();
            $obj->line = $line;
            $obj->time = $timestamp;
            array_push(${$port . '_arr'}, $obj);
        }

        // sort ports low -> high (will go in order of server selection list)
        sort($ports);
        $last_server = end($ports);

        foreach ($ports as $port) {
            $server = server_select_by_port($pdo, $port);
            $server_name = $port === 843 ? 'Policy Server' : htmlspecialchars($server->server_name, ENT_QUOTES);
            $server_name = empty($server) && empty($server_name) ? $port : $server_name . ' (' . $port . ')';

            // start server log list
            echo "<b><u>$server_name</u></b>";
            echo '<ul>';

            // sort logs by start time descending
            usort(${$port . '_arr'}, 'sort_by_obj_time');
            foreach (${$port . '_arr'} as $item) {
                echo "<li>$item->line</li>";
            }

            // end formatting
            echo '</ul>';
        }
    } // get specific log
    else {
        // get file
        $file = @file_get_contents(ROOT_DIR . '/../pr2/log/' . $file);
        if ($file === false) {
            throw new Exception('The requested log file does not exist.');
        } else {
            $file = htmlspecialchars($file, ENT_QUOTES);
        }

        // output file
        echo "<pre>$file</pre>";
        echo '<br><br><a href="javascript:history.back()"><- Go Back</a>';
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error');
    }
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
