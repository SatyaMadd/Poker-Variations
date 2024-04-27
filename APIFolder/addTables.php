<?php
session_start();
try {
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $name = $_GET['name'];
  $_SESSION['name'] = $name;
  $userid = $_GET['userid'];
  $stmt = $db->prepare('INSERT INTO betTrack (name, amount, totalAm) VALUES (:name, :amount, :totalAm)');
  $stmt->execute([':name' => $name, ':amount' => 0, ':totalAm' => 0]);
  $stmt = $db->prepare('SELECT ROWID, * FROM messages WHERE name = :name');
  $stmt->execute([':name' => $name]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $cardNumber1 = rand(1, 13);
  $cardNumber2 = rand(1, 13);
  $suitN = rand(1, 4);
  $suit1 = '';
  $suit2 = '';
  switch ($suitN) {
    case 1:
      $suit1 = 'Hearts';
      break;
    case 2:
      $suit1 = 'Diamonds';
      break;
    case 3:
      $suit1 = 'Spades';
      break;
    case 4:
      $suit1 = 'Clubs';
      break;
  }
  $suitN = rand(1, 4);
  switch ($suitN) {
    case 1:
      $suit2 = 'Hearts';
      break;
    case 2:
      $suit2 = 'Diamonds';
      break;
    case 3:
      $suit2 = 'Spades';
      break;
    case 4:
      $suit2 = 'Clubs';
      break;
  }
  $stmt = $db->query("SELECT COUNT(*) FROM messages");
  $rowCount = $stmt->fetchColumn();
  $turn = ($rowCount == 0) ? 'yes' : 'no';
  $chips = 500;
  if (!$row) {
    $stmt = $db->prepare('INSERT INTO messages (name, userID, cardNumber1, suit1, cardNumber2, suit2, ready, turn, chips, status) VALUES (:name, :userid, :cardNumber1, :suit1, :cardNumber2, :suit2, :ready, :turn, :chips, :status)');
    $stmt->execute([':name' => $name, ':userid' => $userid, ':cardNumber1' => $cardNumber1, ':suit1' => $suit1, ':cardNumber2' => $cardNumber2, ':suit2' => $suit2, ':ready' => 'no', ':turn' => $turn, ':chips' => $chips, ':status' => 'yes']);
  }
  $db = null;
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>
