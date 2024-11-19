<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$host = "193.154.207.221";
$username = "kremsguesser";
$password = "123mysql";
$dbname = "OnlineCasino";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Benutzerinformationen abrufen
$sql = "SELECT balance FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$balance = $result->fetch_assoc()['balance'];
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Casino Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.0/socket.io.min.js"></script>
</head>

<body>
    <header>
        Willkommen im Online-Casino
    </header>
    <div class="container">
        <h1>Ihr Guthaben: <?= number_format($balance, 2) ?> €</h1>
        <div id="live-race">
            <h2>Live-Pferderennen</h2>
            <div id="race-info">Warten auf das nächste Rennen...</div>
            <button onclick="placeBet()">Wette platzieren</button>
        </div>
        <h3>Wette platzieren</h3>
        <select id="bet-choice">
            <option value="Horse A">Horse A</option>
            <option value="Horse B">Horse B</option>
            <option value="Horse C">Horse C</option>
            <option value="Horse D">Horse D</option>
        </select>
        <input type="number" id="bet-amount" placeholder="Einsatz (€)" min="1" max="<?= $balance ?>">
        <button onclick="placeBet()">Wette platzieren</button>
        <div id="bet-result"></div>
    </div>
    <footer>
        &copy; 2024 Online Casino. Alle Rechte vorbehalten.
    </footer>
    <script>
    const socket = io('http://localhost:3000');

    // Echtzeit-Updates vom Rennen
    socket.on('raceUpdate', (data) => {
        if (data.raceInProgress) {
            document.getElementById('race-info').innerHTML = `
                <p>Rennen läuft...</p>
                <p>Positionen: ${data.raceResults.join(', ')}</p>
            `;
        } else {
            document.getElementById('race-info').innerHTML = `
                <p>Kein Rennen im Moment. Warten auf das nächste Rennen...</p>
            `;
        }
    });

    socket.on('raceEnd', (data) => {
        document.getElementById('race-info').innerHTML = `
            <p>Rennen beendet!</p>
            <p>Ergebnisse:</p>
            <ol>
                <li>${data.raceResults[0]}</li>
                <li>${data.raceResults[1]}</li>
                <li>${data.raceResults[2]}</li>
                <li>${data.raceResults[3]}</li>
            </ol>
        `;
    });

    // Wette platzieren
    function placeBet() {
        const choice = document.getElementById('bet-choice').value;
        const amount = document.getElementById('bet-amount').value;

        if (amount > <?= $balance ?>) {
            alert("Du hast nicht genug Guthaben!");
            return;
        }

        fetch('place_bet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `bet_choice=${choice}&bet_amount=${amount}`
        }).then(response => response.text()).then(result => {
            document.getElementById('bet-result').innerHTML = result;
        });
    }
    </script>
</body>

</html>