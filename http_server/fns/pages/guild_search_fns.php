<?php

// guild_search.php
function output_guild_search($guild_name = '', $guild_id = '', $mode = null)
{
    $guild_id = (int) $guild_id;

    // choose which one to set after searching
    $id_display = 'none';
    $name_display = 'none';
    $id_checked = '';
    $name_checked = '';
    switch ($mode) {
        case 'id':
            $id_display = 'block';
            $id_checked = 'checked="checked"';
            break;
        case 'name':
            $name_display = 'block';
            $name_checked = 'checked="checked"';
            break;
    }

    // check if values passed are empty
    if (is_empty($guild_name)) {
        $guild_name = '';
    }
    if (is_empty($guild_id, false)) {
        $guild_id = '';
    }

    // center
    echo '<center>';

    // gwibble, spacing
    echo '<font face="Gwibble" class="gwibble">-- Guild Search --</font><br><br>';

    // javascript to show/hide the name/id textboxes
    echo '<script>
              function name_id_check() {
                  if (document.getElementById("nameradio").checked) {
                      document.getElementById("nameform").style.display = "block";
                      document.getElementById("idform").style.display = "none";
                  }
                  else if (document.getElementById("idradio").checked) {
                  document.getElementById("idform").style.display = "block";
                  document.getElementById("nameform").style.display = "none";
                  }
              }
          </script>';

    // search type selection
    echo 'Search by: '
        ."<input type='radio' onclick='name_id_check()' id='nameradio' name='typeRadio' $name_checked> Name "
        ."<input type='radio' onclick='name_id_check()' id='idradio' name='typeRadio' $id_checked> ID"
        .'<br>';

    // name form
    $html_guild_name = htmlspecialchars($guild_name);
    echo "<div id='nameform' style='display:$name_display'><br>
              <form method='get'>
                  Name: <input type='text' name='name' value='$html_guild_name'>
                        <input type='submit' value='Search'>
              </form>
          </div>";

    // id form
    echo "<div id='idform' style='display:$id_display'><br>
              <form method='get'>
                  ID:
                  <input type='text'
                         name='id'
                         oninput=\"this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\..*)\./g, \'$1\');\"
                         value='$guild_id'>
                  <input type='submit' value='Search'>
              </form>
          </div>";

    // end center
    echo '</center>';
}
