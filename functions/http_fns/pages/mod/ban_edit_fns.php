<?php

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

// mod/ban_edit.php
function get_lifted_html($lifted = false, $lifted_reason = '')
{
    $lifted = (bool) $lifted;
    $lifted_reason = (string) $lifted_reason;

    // choose whether to display at first
    $disp = check_value($lifted, true, '', 'display: none;');
    $checked = check_value($lifted, true, 'checked="checked"', '');

    return 'Lifted '
        ."<input type='checkbox' id='lifted' name='lifted' onclick='lifted_check();' $checked> "
        ."<input type='text' id='lifted_reason' name='lifted_reason' size='50' value='$lifted_reason' style='$disp'>";
}
