<?php

// this function should eventually be replaced with just password_verify
function deprecated_password_verify ($pass, $target_hash) {
  global $PASS_SALT;
  $deprecated_hash = sha1($pass . $PASS_SALT);
  return $deprecated_hash === $target_hash;
}

?>
