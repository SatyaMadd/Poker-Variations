<?php
session_start();
try {
  $db = new PDO('sqlite:APIFolder/database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
  $stmt = $db->query("SELECT COUNT(*) FROM globalV");
  $count = $stmt->fetchColumn();
  if ($count == 0) {
    $stmt = $db->prepare("INSERT INTO globalV (round, turns, pot) VALUES (:round, :turns, :pot)");
    $stmt->execute(['round' => 1, 'turns' => 0, 'pot' => 0]);
  }
  $insert = $db->prepare("INSERT INTO totCards (cardNumber, suit) VALUES (?, ?)");
  for ($suit = 1; $suit <= 4; $suit++) {
    for ($cardNumber = 1; $cardNumber <= 13; $cardNumber++) {
      $insert->execute([$cardNumber, $suit]);
    }
  }
  $_SESSION['messages'] = $db->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);
  $_SESSION['messages'];
  $_SESSION['name'];
  $db = null;
} catch (PDOException $ex) {
  echo $ex->getMessage();
}
if (!isset($_SESSION['myId'])) {
    $_SESSION['myId'] = uniqId();
}
?>
<html>
  <head>
    <title>PHP Test</title>
  </head>
  <body>
    <h1 id="myheader">Home</h1>
    <button type = "button" onclick = 
        "clearDatabase()">
        Reset
    </button>
    <br>
    <h4>Type your username</h4>
    <input type="text" id="usernameIn" style="width: 75px;">
    <br>
    <br>
    <button type = "button" onclick = "addrw()">Submit</button>
    <script>
      function addrw(){
        let usrinput = document.getElementById("usernameIn").value.trim();
        let myId = <?php echo json_encode($_SESSION['myId']); ?>;
        if(usrinput!=""){
          const xhttp = new XMLHttpRequest();
          xhttp.onload = function() {
            window.location.href = "gameroom.php";
          }
          xhttp.open("GET", "APIFolder/addTables.php?name=" + usrinput + "&userid=" + myId, true);
          xhttp.send();
        }
      }
      function clearDatabase(){
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", "APIFolder/clear.php", true);
        xhttp.send();
      }
    </script>
  </body>
</html>