<?php
session_start();
$host = "193.154.207.221";
$username = "kremsguesser";
$password = "123mysql";
$dbname = "OnlineCasino";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$race_id = $_POST['race_id'];
$bet_choice = $_POST['bet_choice'];
$bet_amount = $_POST['bet_amount'];

// Benutzerbalance prüfen
$sql = "SELECT balance FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['balance'] >= $bet_amount) {
    // Guthaben aktualisieren und Wette platzieren
    $sql = "UPDATE users SET balance = balance - $bet_amount WHERE id = $user_id";
    $conn->query($sql);

    $sql = "INSERT INTO bets (user_id, race_id, bet_choice, amount) 
            VALUES ($user_id, $race_id, '$bet_choice', $bet_amount)";
    if ($conn->query($sql) === TRUE) {
        echo "Wette erfolgreich platziert.";
    } else {
        echo "Fehler: " . $conn->error;
    }
} else {
    echo "Nicht genügend Guthaben.";
}

$conn->close();
?>
