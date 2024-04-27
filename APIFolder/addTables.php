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
  $select = $db->query("SELECT * FROM totCards ORDER BY RANDOM() LIMIT 2");
  $cards = $select->fetchAll(PDO::FETCH_ASSOC);
  $delete = $db->prepare("DELETE FROM totCards WHERE id = ?");
  foreach ($cards as $card) {
    $delete->execute([$card['id']]);
  }
  $card1 = $cards[0];
  $card2 = $cards[1];
  $suits = ['1' => 'Hearts', '2' => 'Diamonds', '3' => 'Spades', '4' => 'Clubs'];
  $cardNumber1 = $card1['cardNumber'];
  $cardNumber2 = $card2['cardNumber'];
  $suit1 = $suits[$card1['suit']];
  $suit2 = $suits[$card2['suit']];
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
