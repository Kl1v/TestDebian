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

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Aktuellen Kontostand des Nutzers abrufen
$sql_balance = "SELECT balance FROM users WHERE id = $user_id";
$result_balance = $conn->query($sql_balance);
$balance = $result_balance->fetch_assoc()['balance'];

// Nächste Rennen abrufen
$sql_races = "SELECT * FROM horse_races WHERE race_time > NOW() ORDER BY race_time ASC";
$result_races = $conn->query($sql_races);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casino Dashboard</title>
    <script>
        async function placeBet(raceId) {
            const betChoice = document.getElementById(`bet_choice_${raceId}`).value;
            const betAmount = document.getElementById(`bet_amount_${raceId}`).value;

            const response = await fetch('place_bet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `race_id=${raceId}&bet_choice=${betChoice}&bet_amount=${betAmount}`
            });

            const result = await response.text();
            alert(result);
            location.reload(); // Seite neu laden, um den aktualisierten Kontostand zu zeigen
        }
    </script>
</head>
<body>
    <h1>Willkommen im Online Casino</h1>
    <h2>Ihr aktuelles Guthaben: <?= number_format($balance, 2) ?> €</h2>

    <h2>Nächste Pferderennen</h2>
    <?php if ($result_races->num_rows > 0): ?>
        <?php while ($race = $result_races->fetch_assoc()): ?>
            <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
                <h3>Rennen um <?= date('H:i:s', strtotime($race['race_time'])) ?></h3>
                <label for="bet_choice_<?= $race['id'] ?>">Wählen Sie ein Pferd:</label>
                <select id="bet_choice_<?= $race['id'] ?>">
                    <option value="Horse A">Horse A</option>
                    <option value="Horse B">Horse B</option>
                    <option value="Horse C">Horse C</option>
                    <option value="Horse D">Horse D</option>
                </select>
                <label for="bet_amount_<?= $race['id'] ?>">Wetteinsatz (€):</label>
                <input type="number" id="bet_amount_<?= $race['id'] ?>" min="1" max="<?= $balance ?>" />
                <button onclick="placeBet(<?= $race['id'] ?>)">Wette platzieren</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Derzeit sind keine Rennen verfügbar.</p>
    <?php endif; ?>

    <a href="bet_history.php">Wettverlauf ansehen</a>
</body>
</html>

<?php $conn->close(); ?>
