<?php
session_start();
try {
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $name = $_GET['name'];
  $betAm = $_GET['betAmount'];
  $db->beginTransaction();
  $stmt = $db->prepare("SELECT amount FROM betTrack ORDER BY id DESC LIMIT 1");
  $stmt->execute();
  $currentBet = $stmt->fetchColumn();
  if ($currentBet>=$betAm){
    echo 'n';
  }else{
    $stmt = $db->prepare("UPDATE messages SET turn = 'no' WHERE turn = 'yes'");
    $stmt->execute();
    $stmt = $db->prepare("UPDATE globalV SET turns = 1 WHERE id = 1");
    $stmt->execute(); 
    $stmt = $db->prepare("SELECT id FROM messages ORDER BY id ASC");
    $stmt->execute();
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $stmt = $db->prepare("SELECT id FROM messages WHERE name = :name");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $currentId = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT amount FROM betTrack WHERE name = :name ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $lastBet = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT chips FROM messages WHERE name = :name");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $currChips = $stmt->fetchColumn();
    $adjustedBetAm = min(($betAm - $lastBet), $currChips);
    $betAm = $adjustedBetAm + $lastBet;
    $stmt = $db->prepare("UPDATE messages SET chips = chips - :adjustedBetAm WHERE id = :currentId");
    $stmt->bindParam(':adjustedBetAm', $adjustedBetAm);
    $stmt->bindParam(':currentId', $currentId);
    $stmt->execute();
    $key = array_search($currentId, $ids);
    if ($key !== false) {
        $nextId = isset($ids[$key + 1]) ? $ids[$key + 1] : $ids[0];
        $stmt = $db->prepare("SELECT status FROM messages WHERE id = :id");
        $stmt->bindParam(':id', $nextId);
        $stmt->execute();
        $status = $stmt->fetchColumn();
        while ($status == 'no') {
            $key = array_search($nextId, $ids);
            $nextId = isset($ids[$key + 1]) ? $ids[$key + 1] : $ids[0];
            $stmt->bindParam(':id', $nextId);
            $stmt->execute();
            $status = $stmt->fetchColumn();
        }
        $stmt = $db->prepare("UPDATE messages SET turn = 'yes' WHERE id = :id");
        $stmt->bindParam(':id', $nextId);
        $stmt->execute();
    }
    $stmt = $db->prepare("SELECT totalAm FROM betTrack WHERE name = :name ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $currAm = $stmt->fetchColumn();
    $actualAmt = $currAm + $adjustedBetAm;
    $stmt = $db->prepare("INSERT INTO betTrack (name, amount, totalAm) VALUES (:name, :betAm, :totalAm)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':betAm', $betAm);
    $stmt->bindParam(':totalAm', $actualAm); 
    $stmt->execute();
    $stmt = $db->prepare("UPDATE globalV SET pot = pot + :adjustedBetAm WHERE id = 1");
    $stmt->bindParam(':adjustedBetAm', $adjustedBetAm);
    $stmt->execute(); 
    $db->commit();
    $_SESSION['messages'] = $db->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);
    $globalVar = $db->query("SELECT * FROM globalV")->fetchAll(PDO::FETCH_ASSOC);
    $betTrackAr = $db->query("SELECT * FROM betTrack")->fetchAll(PDO::FETCH_ASSOC);
    $commCards = $db->query("SELECT * FROM cards")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array('messages' => $_SESSION['messages'], 'globalVar' => $globalVar, 'betTrackAr' => $betTrackAr, 'cards' => $commCards));
  }
} catch (PDOException $e) {
  $db->rollback();
  echo "Error: " . $e->getMessage();
}
?>
