<?php

function output_objects($objs, $is_logins = false, $user = null)
{
    if ($objs !== false) {
        $count = 0;
        foreach ($objs as $obj) {
            output_object($obj, ', ');
            $count++;
        }
        if ($is_logins === true && $count > 0) {
            $url_name = urlencode($user->name);
            echo "<a href='player_deep_logins.php?name=$url_name'>more logins</a><br>";
        }
    }
}

function output_object($obj, $sep = '<br/>')
{
    if ($obj !== false) {
        foreach ($obj as $var => $val) {
            if ($var === 'email' || $var === 'old_email' || $var === 'new_email') {
                $safe_email = htmlspecialchars($val, ENT_QUOTES);
                $url_email = urlencode($val);
                $val = "<a href='search_by_email.php?email=$url_email'>$safe_email</a>";
                echo "$var: $val$sep";
                continue;
            } elseif ($var === 'ip' || $var === 'register_ip' || $var === 'request_ip' || $var === 'confirm_ip') {
                $safe_ip = htmlspecialchars($val, ENT_QUOTES);
                $url_ip = urlencode($val);
                $val = "<a href='/mod/ip_info.php?ip=$url_ip'>$safe_ip</a>";
                echo "$var: $val$sep";
                continue;
            } elseif ($var === 'guild') {
                $val = (int) $val;
                $val = $val !== 0 ? "<a href='guild_deep_info.php?guild_id=$val'>$val</a>" : 'none';
                echo "$var: $val$sep";
                continue;
            } elseif ($var === 'coins') {
                $val = number_format((int) $val);
                echo "$var: $val - <a href='award_coins.php?user_id=$obj->user_id'>award</a>$sep";
                continue;
            } elseif ($var === 'level_count') {
                echo "$var: $val - <a href='javascript:alert(\"Coming soon!\")'>view all</a>$sep";
                continue;
            } elseif ($var === 'time' || $var === 'register_time') {
                $val = date('M j, Y g:i A', $val);
            }
            if ($var !== 'user_id') {
                echo "$var: ".htmlspecialchars($val, ENT_QUOTES)."$sep";
            }
        }
        if ($sep !== '<br/>') {
            echo '<br>';
        }
    }
}

function output_object_keys($obj, $sep = ', ')
{
    if ($obj !== false && $obj !== null) {
        foreach ($obj as $var => $val) {
            if ($val == 1 && $var !== 'user_id' && $var !== 'hat_array') {
                echo $var.$sep;
            }
        }
        echo '<br>';
    }
}
