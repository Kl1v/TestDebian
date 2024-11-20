<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht eingeloggt.");
}

$userId = $_SESSION['user_id'];
$betChoice = $_POST['bet_choice'];
$betAmount = $_POST['bet_amount'];

// Guthaben prüfen
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $userId]);
$balance = $stmt->fetchColumn();

if ($betAmount > $balance) {
    die("Nicht genug Guthaben.");
}

// Aktuelles Rennen abrufen
$stmt = $pdo->prepare("SELECT * FROM horse_races WHERE race_time > NOW() ORDER BY race_time ASC LIMIT 1");
$stmt->execute();
$currentRace = $stmt->fetch();

if (!$currentRace) {
    die("Kein Rennen verfügbar.");
}

// Wette speichern
$stmt = $pdo->prepare("INSERT INTO bets (user_id, race_id, bet_choice, amount) VALUES (:user_id, :race_id, :bet_choice, :amount)");
$stmt->execute([
    'user_id' => $userId,
    'race_id' => $currentRace['id'],
    'bet_choice' => $betChoice,
    'amount' => $betAmount
]);

// Guthaben aktualisieren
$stmt = $pdo->prepare("UPDATE users SET balance = balance - :amount WHERE id = :user_id");
$stmt->execute([
    'amount' => $betAmount,
    'user_id' => $userId
]);

echo "Wette auf $betChoice für $betAmount € erfolgreich platziert!";
?>
