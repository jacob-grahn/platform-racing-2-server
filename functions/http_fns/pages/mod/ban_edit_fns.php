<?php


// mod/ban_edit.php
function get_exp_date_html($expire_date)
{
    $placeholder = 'placeholder="YYYY-MM-DD HH:MM:SS"';
    return "<input type='text' name='expire_time' id='expire_time' value='$expire_date' $placeholder>";
}


function get_type_html($ip_ban = false, $acc_ban = false)
{
    if ($ip_ban && $acc_ban) {
        $type = 'both';
    } elseif (!$ip_ban && !$acc_ban) {
        $type = 'none';
    } elseif ($ip_ban && !$acc_ban) {
        $type = 'ip';
    } elseif (!$ip_ban && $acc_ban) {
        $type = 'acc';
    }

    $sel_both = $sel_ip = $sel_acc = $sel_none = '';
    ${'sel_' . $type} = ' selected="selected"';

    return '<select id="type" name="type">'
        ."<option value='both'$sel_both>Account and IP</option>"
        ."<option value='acc'$sel_acc>Account Only</option>"
        ."<option value='ip'$sel_ip>IP Only</option>"
        ."<option value='none'$sel_none>None (remove prior)</option>"
        .'</select>';
}


function get_scope_html($scope)
{
    $sel_s = $sel_g = '';
    ${'sel_' . $scope} = ' selected="selected"';

    return '<select name="scope">'
        ."<option value='g'$sel_g>Game</option>"
        ."<option value='s'$sel_s>Social</option>"
        .'</select>';
}


function get_lifted_js()
{
    return '<script>
              function lifted_check() {
                  if (document.getElementById("lifted").checked) {
                      document.getElementById("lifted_reason").style.display = "";
                  } else {
                      document.getElementById("lifted_reason").style.display = "none";
                  }
              }
            </script>';
}


function get_lifted_html($lifted = false, $lifted_reason = '')
{
    $lifted = (bool) $lifted;
    $lifted_reason = (string) $lifted_reason;

    // choose whether to display at first
    $disp = check_value($lifted, true, '', 'display: none;');
    $checked = check_value($lifted, true, 'checked="checked"', '');

    return "<input type='checkbox' id='lifted' name='lifted' onclick='lifted_check();' $checked> "
        ."<input type='text' id='lifted_reason' name='lifted_reason' size='50' value='$lifted_reason' style='$disp'>";
}
