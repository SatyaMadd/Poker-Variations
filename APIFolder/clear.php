<?php
session_start();
try {
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->exec(
    "DROP TABLE IF EXISTS messages;"
  );
  $db->exec(
    "DROP TABLE IF EXISTS globalV;"
  );
  $db->exec(
    "DROP TABLE IF EXISTS betTrack;"
  );
  $db->exec(
    "DROP TABLE IF EXISTS cards;"
  );
  $db->exec(
    "DROP TABLE IF EXISTS winners;"
  );
  $res = $db->exec(
    "CREATE TABLE IF NOT EXISTS messages (    
      id INTEGER PRIMARY KEY,
      name TEXT, 
      userID TEXT,
      cardNumber1 INTEGER,
      suit1 TEXT,
      cardNumber2 INTEGER,
      suit2 TEXT,
      ready TEXT,
      turn TEXT,
      chips INTEGER,
      status TEXT
    )"
  );
  $res = $db->exec(
    "CREATE TABLE IF NOT EXISTS globalV (    
      id INTEGER PRIMARY KEY,
      round INTEGER,
      turns INTEGER,
      pot INTEGER
    )"
  );
  $res = $db->exec(
  "CREATE TABLE IF NOT EXISTS betTrack (    
    id INTEGER PRIMARY KEY,
    name TEXT,
    amount INTEGER,
    totalAm INTEGER
  )"
  );
  $res = $db->exec(
  "CREATE TABLE IF NOT EXISTS cards(    
    id INTEGER PRIMARY KEY,
    cardNumber INTEGER,
    suit TEXT
  )"
  );
  $res = $db->exec(
  "CREATE TABLE IF NOT EXISTS totCards(    
    id INTEGER PRIMARY KEY,
    cardNumber INTEGER,
    suit INTEGER
  )"
  );
  $res = $db->exec(
  "CREATE TABLE IF NOT EXISTS winners(    
    id INTEGER PRIMARY KEY,
    name TEXT,
    score DECIMAL(13, 12)
  )"
  );
  $stmt = $db->prepare("INSERT INTO globalV (round, turns, pot) VALUES (:round, :turns, :pot)");
  $stmt->execute(['round' => 1, 'turns' => 0, 'pot' => 0]);
  $insert = $db->prepare("INSERT INTO totCards (cardNumber, suit) VALUES (?, ?)");
  for ($suit = 1; $suit <= 4; $suit++) {
    for ($cardNumber = 1; $cardNumber <= 13; $cardNumber++) {
      $insert->execute([$cardNumber, $suit]);
    }
  }
  $_SESSION['messages'] = $db->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);
  $globalVar = $db->query("SELECT * FROM globalV")->fetchAll(PDO::FETCH_ASSOC);
  $betTrackAr = $db->query("SELECT * FROM betTrack")->fetchAll(PDO::FETCH_ASSOC);
  $commCards = $db->query("SELECT * FROM cards")->fetchAll(PDO::FETCH_ASSOC);
  $db = null;
  echo json_encode(array('messages' => $_SESSION['messages'], 'globalVar' => $globalVar, 'betTrackAr' => $betTrackAr, 'cards' => $commCards));
} catch (PDOException $ex) {
  echo $ex->getMessage();
}
?>