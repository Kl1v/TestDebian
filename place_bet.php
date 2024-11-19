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
$bet_choice = $_POST['bet_choice'];
$bet_amount = $_POST['bet_amount'];

// Benutzerguthaben abfragen
$sql = "SELECT balance FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$balance = $row['balance'];

// Prüfen, ob der Benutzer genug Guthaben hat
if ($bet_amount > $balance) {
    echo "Du hast nicht genug Guthaben!";
    exit();
}

// Wette speichern
$sql = "INSERT INTO bets (user_id, bet_choice, bet_amount) VALUES ($user_id, '$bet_choice', $bet_amount)";
$conn->query($sql);

// Guthaben aktualisieren
$new_balance = $balance - $bet_amount;
$sql = "UPDATE users SET balance = $new_balance WHERE id = $user_id";
$conn->query($sql);

echo "Wette auf $bet_choice in Höhe von $bet_amount € erfolgreich platziert!";
?>