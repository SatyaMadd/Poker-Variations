<?php
try {
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $name = $_GET['name'];
  $stmt = $db->prepare("UPDATE messages SET ready = 'yes' WHERE name = :name");
  $stmt->execute([':name' => $name]);
  $_SESSION['messages'] = $db->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);
  $globalVar = $db->query("SELECT * FROM globalV")->fetchAll(PDO::FETCH_ASSOC);
  $betTrackAr = $db->query("SELECT * FROM betTrack")->fetchAll(PDO::FETCH_ASSOC);
  $commCards = $db->query("SELECT * FROM cards")->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(array('messages' => $_SESSION['messages'], 'globalVar' => $globalVar, 'betTrackAr' => $betTrackAr, 'cards' => $commCards));
} catch (PDOException $ex) {
  echo $ex->getMessage();
}
?>
