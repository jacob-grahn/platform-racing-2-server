<?php

function retrieve_ban_list($pdo, $start, $count) {
  $stmt = $pdo->prepare('SELECT * FROM bans ORDER BY time DESC LIMIT ?, ?');
  $stmt->bindValue(1, $start, PDO::PARAM_INT);
  $stmt->bindValue(2, $count, PDO::PARAM_INT);
  $stmt->execute();
}

?>
