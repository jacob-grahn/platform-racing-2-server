<?php

function level_unpublish ($pdo, $level_id) {
  $stmt = $pdo->prepare('UPDATE pr2_levels SET live = 0, pass = NULL WHERE level_id = ?');
  $stmt->execute([$level_id]);
}

?>
