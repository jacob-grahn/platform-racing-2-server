<?php

function random_str ($bytes) {
  return bin2hex(random_bytes($bytes));
}

?>
