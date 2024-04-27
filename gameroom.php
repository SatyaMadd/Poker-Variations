<?php
session_start();
try {
  $db = new PDO('sqlite:APIFolder/database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $_SESSION['messages'] = $db->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);
  $db = null;
} catch (PDOException $ex) {
  echo $ex->getMessage();
}
?>
<html>
  <head>
    <title>Game Room</title>
  </head>
  <body onload = "refresh()">
    <h1 id="myheader">Game Room</h1>
    <button type = "button" onclick = 
        "returnback()">
        Return
    </button>
    <br>
    <h4 id="nameShow">You are <?=$_SESSION['name']?></h4>
    <h4 id="roundN"></h4>
    <h4 id="potAm"></h4>
    <button type = "button" onclick = "refresh()">Refresh</button>
    <br>
    <button type = "button" id = "readyB" onclick = "readyUp()">Ready Up</button>
    <br>
    <button type = "button" id = "checkB" onclick = "check()">Check</button>
    <br>
    <input type="text" id="betAm" style="width: 50px;">
    <button type = "button" id = "betB" onclick = "bet()">Bet</button>
    <button type = "button" id = "foldB" onclick = "fold()">Fold</button>
    <script>
      function refresh(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
          createTable(JSON.parse(this.responseText), "<?=$_SESSION['name']?>");
        }
        xhttp.open("GET", "APIFolder/refresh.php", true);
        xhttp.send();
      }
      function returnback(){
        window.location.href = "index.php";
      }
      function readyUp(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
          createTable(JSON.parse(this.responseText), "<?=$_SESSION['name']?>");
        }
        xhttp.open("GET", "APIFolder/ready.php?name=" + "<?=$_SESSION['name']?>", true);
        xhttp.send();
      }
      function check(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
          createTable(JSON.parse(this.responseText), "<?=$_SESSION['name']?>");
        }
        xhttp.open("GET", "APIFolder/check.php?name=" + "<?=$_SESSION['name']?>", true);
        xhttp.send();
      }
      function bet(){
        let betAmount = document.getElementById("betAm").value.trim();
        if(!isNaN(betAmount)){
          betAmount = parseInt(betAmount);
          const xhttp = new XMLHttpRequest();
          xhttp.onload = function() {
            if(this.responseText!='n'){
              createTable(JSON.parse(this.responseText), "<?=$_SESSION['name']?>");
            }
          }
          xhttp.open("GET", "APIFolder/bet.php?name=" + "<?=$_SESSION['name']?>"+ "&betAmount=" + betAmount, true);
          xhttp.send();
        }
      }
      function fold(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
          createTable(JSON.parse(this.responseText), "<?=$_SESSION['name']?>");
        }
        xhttp.open("GET", "APIFolder/fold.php?name=" + "<?=$_SESSION['name']?>", true);
        xhttp.send();
      }
    </script>
    <script src="createTable.js"></script>
  </body>
</html>