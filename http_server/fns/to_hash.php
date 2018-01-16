<?php

function to_hash ($pass) {
  return password_hash(sha1($pass), PASSWORD_BCRYPT, ['cost' => 12]);
}

?>
