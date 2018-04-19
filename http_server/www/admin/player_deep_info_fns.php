<?php

function output_objects($objs, $is_logins = false, $user = null, $keys = false)
{
    if ($objs !== false) {
        foreach ($objs as $obj) {
            if ($keys === false) {
                output_object($obj, ', ');
            } elseif ($keys === true) {
                output_object_keys($obj, ', ');
            }
            echo '<br>';
        }
        if ($is_logins === true) {
            $url_name = urlencode($user->name);
            echo "<a href='player_deep_logins.php?name=$url_name'>more logins</a><br>";
        }
    }
}

function output_object($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($var == 'email') {
                $safe_email = htmlspecialchars($val);
                $url_email = urlencode($val);
                $val = "<a href='search_by_email.php?email=$url_email'>$safe_email</a>";
                echo "$var: $val $sep";
            }
            if ($var == 'guild') {
                $val = (int) $val;
                if ($val != 0) {
                    $val = "<a href='guild_deep_info.php?guild_id=$val'>$val</a>";
                } else {
                    $val = 'none';
                }
                echo "$var: $val $sep";
            }
            if ($var == 'time' || $var == 'register_time') {
                $val = date('M j, Y g:i A', $val);
            }
            if ($var != 'user_id' && $var != 'email' && $var != 'guild') {
                echo "$var: ".htmlspecialchars($val)."$sep";
            }
        }
    }
}

function output_object_keys($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($val == 1 && $var != 'user_id') {
                echo $val.$sep;
            }
        }
    }
}
