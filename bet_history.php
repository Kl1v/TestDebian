<?php
session_start();
$host = "193.154.207.221";
$username = "kremsguesser";
$password = "123mysql";
$dbname = "OnlineCasino";

$conn = new mysqli($host, $username, $password, $dbname);
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM bets WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);

echo "<h1>Wettverlauf</h1>";
while ($row = $result->fetch_assoc()) {
    echo "<div>
            Rennen ID: {$row['race_id']} <br>
            Gewähltes Pferd: {$row['bet_choice']} <br>
            Einsatz: {$row['amount']} € <br>
            Ergebnis: {$row['result']}
          </div><hr>";
}

$conn->close();
?>
