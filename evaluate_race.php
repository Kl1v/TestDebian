<?php
$host = "193.154.207.221";
$username = "kremsguesser";
$password = "123mysql";
$dbname = "OnlineCasino";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Alle Rennen, die ausgewertet werden müssen
$sql = "SELECT * FROM horse_races WHERE race_time <= NOW() AND id NOT IN (SELECT race_id FROM bets WHERE result != 'pending')";
$result = $conn->query($sql);

while ($race = $result->fetch_assoc()) {
    $race_id = $race['id'];
    $results = json_decode($race['result'], true);
    $winning_horse = $results['1st'];

    // Gewinne auszahlen
    $sql_bets = "SELECT * FROM bets WHERE race_id = $race_id";
    $bets = $conn->query($sql_bets);

    while ($bet = $bets->fetch_assoc()) {
        if ($bet['bet_choice'] == $winning_horse) {
            $win_amount = $bet['amount'] * 2; // Beispiel: 2x Auszahlung
            $user_id = $bet['user_id'];

            // Guthaben aktualisieren
            $sql_update_balance = "UPDATE users SET balance = balance + $win_amount WHERE id = $user_id";
            $conn->query($sql_update_balance);

            // Wette als gewonnen markieren
            $sql_update_bet = "UPDATE bets SET result = 'win' WHERE id = " . $bet['id'];
            $conn->query($sql_update_bet);
        } else {
            // Wette als verloren markieren
            $sql_update_bet = "UPDATE bets SET result = 'loss' WHERE id = " . $bet['id'];
            $conn->query($sql_update_bet);
        }
    }
}

$conn->close();
?>
