<?php
session_start();
try {
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $name = $_GET['name'];
  $db->beginTransaction();
  $stmt = $db->prepare("UPDATE messages SET status = 'no' WHERE turn = 'yes'");
  $stmt->execute();
  $stmt = $db->prepare("UPDATE messages SET turn = 'no' WHERE turn = 'yes'");
  $stmt->execute(); 
  $stmt = $db->prepare("SELECT turns FROM globalV WHERE id = 1");
  $stmt->execute();
  $currentTurns = $stmt->fetchColumn();   
  $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE status = 'yes'");
  $stmt->execute();
  $messageCount = $stmt->fetchColumn();
  if ($currentTurns == $messageCount) {
    $stmt = $db->prepare("UPDATE globalV SET turns = 0, round = round + 1 WHERE id = 1");
    $stmt->execute();
  }
  $stmt = $db->prepare("SELECT round FROM globalV WHERE id = 1");
  $stmt->execute();
  $currentRounds = $stmt->fetchColumn();
  if(($currentRounds>4)||($messageCount==1)){
    $stmt = $db->prepare("UPDATE messages SET status = 'yes' WHERE status = 'no'");
    $stmt->execute();
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE status = 'yes'");
    $stmt->execute();
    $messageCount = $stmt->fetchColumn();
    $stmt = $db->prepare("UPDATE globalV SET turns = 0, round = 1 WHERE id = 1");
    $stmt->execute();
    $stmt = $db->prepare("SELECT pot FROM globalV WHERE id = 1");
    $stmt->execute();
    $pot = $stmt->fetchColumn();
    $randomPlayerId = rand(1, $messageCount);
    $stmt = $db->prepare("UPDATE messages SET chips = chips + :pot WHERE id = :randomPlayerId");
    $stmt->bindParam(':pot', $pot);
    $stmt->bindParam(':randomPlayerId', $randomPlayerId);
    $stmt->execute();
    $stmt = $db->prepare("UPDATE globalV SET pot = 0 WHERE id = 1");
    $stmt->execute();
    $stmt = $db->prepare("
        DELETE FROM betTrack 
        WHERE id NOT IN (
            SELECT id FROM betTrack ORDER BY id ASC LIMIT :messageCount
        )
    ");
    $stmt->bindParam(':messageCount', $messageCount, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $db->prepare("
        DELETE FROM cards
    ");
    $stmt->execute();
    $stmt = $db->prepare("UPDATE betTrack SET totalAm = 0");
    $stmt->execute();
  }
  $stmt = $db->prepare("SELECT id FROM messages ORDER BY id ASC");
  $stmt->execute();
  $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
  $stmt = $db->prepare("SELECT id FROM messages WHERE name = :name");
  $stmt->bindParam(':name', $name);
  $stmt->execute();
  $currentId = $stmt->fetchColumn();
  if ($currentTurns == $messageCount) {
    $stmt = $db->prepare("SELECT name FROM betTrack ORDER BY id ASC LIMIT :messageCount");
    $stmt->bindParam(':messageCount', $messageCount, PDO::PARAM_INT);
    $stmt->execute();
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    foreach ($names as $namex) {
        $stmt = $db->prepare("SELECT totalAm FROM betTrack WHERE name = :name ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(':name', $namex);
        $stmt->execute();
        $totalAmTemp = $stmt->fetchColumn();
        $stmt = $db->prepare("SELECT id FROM betTrack WHERE name = :name ORDER BY id ASC LIMIT 1");
        $stmt->bindParam(':name', $namex);
        $stmt->execute();
        $firstId = $stmt->fetchColumn();
        $stmt = $db->prepare("UPDATE betTrack SET totalAm = totalAm + :totalAmTemp WHERE id = :firstId");
        $stmt->bindParam(':totalAmTemp', $totalAmTemp);
        $stmt->bindParam(':firstId', $firstId);
        $stmt->execute();
    }
    $stmt = $db->prepare("
        DELETE FROM betTrack 
        WHERE id NOT IN (
            SELECT id FROM betTrack ORDER BY id ASC LIMIT   :messageCount
        )
    ");
    $stmt->bindParam(':messageCount', $messageCount, PDO::PARAM_INT);
    $stmt->execute();
  }  
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
  $db->commit();
  $_SESSION['messages'] = $db->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);
  $globalVar = $db->query("SELECT * FROM globalV")->fetchAll(PDO::FETCH_ASSOC);
  $betTrackAr = $db->query("SELECT * FROM betTrack")->fetchAll(PDO::FETCH_ASSOC);
  $commCards = $db->query("SELECT * FROM cards")->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(array('messages' => $_SESSION['messages'], 'globalVar' => $globalVar, 'betTrackAr' => $betTrackAr, 'cards' => $commCards));
} catch (PDOException $e) {
  $db->rollback();
  echo "Error: " . $e->getMessage();
}
?>
