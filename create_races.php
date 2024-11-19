<?php
$host = "193.154.207.221";
$username = "kremsguesser";
$password = "123mysql";
$dbname = "OnlineCasino";

// Verbindung zur Datenbank herstellen
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Simulation des Rennens
$horses = ['Horse A', 'Horse B', 'Horse C', 'Horse D'];
shuffle($horses);
$result = json_encode([
    '1st' => $horses[0],
    '2nd' => $horses[1],
    '3rd' => $horses[2]
]);

$race_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
$sql = "INSERT INTO horse_races (race_time, result) VALUES ('$race_time', '$result')";
if ($conn->query($sql) === TRUE) {
    echo "Neues Rennen erstellt.";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>