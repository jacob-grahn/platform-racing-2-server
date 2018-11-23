<?php

function output_objects($objs)
{
    if ($objs !== false) {
        foreach ($objs as $obj) {
            output_object($obj, ', ');
            echo '<br/>';
        }
    }
}

function output_object($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($var == 'name') {
                $safe_val = htmlspecialchars($val, ENT_QUOTES);
                $url_val = urlencode($val);
                $val = "<a href='player_deep_info.php?name1=$url_val'>$safe_val</a>";
                echo "$var: $val$sep";
                continue;
            } elseif ($var == 'request_ip' || $var == 'confirm_ip') {
                $safe_ip = htmlspecialchars($val, ENT_QUOTES);
                $url_ip = urlencode($val);
                $val = "<a href='player_deep_info.php?name1=$url_ip'>$safe_ip</a>";
                echo "$var: $val$sep";
                continue;
            }
            if ($var != 'guild_id' && $var != 'name') {
                $safe_val = htmlspecialchars($val, ENT_QUOTES);
                echo "$var: $safe_val$sep";
            }
        }
    }
}
