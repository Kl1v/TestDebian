<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = '193.154.207.221';
$dbname = 'Casino';
$username = 'root';
$password = 'password';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user balance
$user_id = $_SESSION['user_id'];
$sql = "SELECT balance FROM Users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $balance = $row['balance'];
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pferderennen Casino</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #87ceeb;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .info {
            margin-bottom: 20px;
        }
        .race-track {
            position: relative;
            width: 100%;
            height: 500px;
            background-color: #006400;
            border-radius: 10px;
            overflow: hidden;
            border: 4px solid #333;
        }
        .horse {
            position: absolute;
            width: 80px;
            height: 50px;
            transition: transform 0.1s;
        }
        .track-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: #fff;
        }
        #result {
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pferderennen Casino</h1>
        <div class="info">
            <p>Guthaben: <span id="balance">$1000</span></p>
            <label for="bet-amount">Wetteinsatz:</label>
            <input type="number" id="bet-amount" placeholder="Betrag" min="1">
            <label for="horse-select">Wähle ein Pferd:</label>
            <select id="horse-select">
                <option value="1">Pferd 1</option>
                <option value="2">Pferd 2</option>
                <option value="3">Pferd 3</option>
                <option value="4">Pferd 4</option>
                <option value="5">Pferd 5</option>
                <option value="6">Pferd 6</option>
                <option value="7">Pferd 7</option>
                <option value="8">Pferd 8</option>
            </select>
            <button id="start-race">Rennen starten</button>
        </div>
        <div class="race-track">
            <div class="track-line" style="top: 62.5px;"></div>
            <div class="track-line" style="top: 125px;"></div>
            <div class="track-line" style="top: 187.5px;"></div>
            <div class="track-line" style="top: 250px;"></div>
            <div class="track-line" style="top: 312.5px;"></div>
            <div class="track-line" style="top: 375px;"></div>
            <div class="track-line" style="top: 437.5px;"></div>
            <div class="horse" id="horse1" style="top: 25px;">🐎</div>
            <div class="horse" id="horse2" style="top: 87.5px;">🐴</div>
            <div class="horse" id="horse3" style="top: 150px;">🦄</div>
            <div class="horse" id="horse4" style="top: 212.5px;">🐎</div>
            <div class="horse" id="horse5" style="top: 275px;">🐴</div>
            <div class="horse" id="horse6" style="top: 337.5px;">🦄</div>
            <div class="horse" id="horse7" style="top: 400px;">🐎</div>
            <div class="horse" id="horse8" style="top: 462.5px;">🐴</div>
        </div>
        <div id="result"></div>
    </div>
    <script>
        let balance = 1000;
        document.getElementById("balance").innerText = `$${balance}`;

        document.getElementById("start-race").addEventListener("click", () => {
            const betAmount = parseInt(document.getElementById("bet-amount").value);
            const selectedHorse = parseInt(document.getElementById("horse-select").value);

            if (isNaN(betAmount) || betAmount <= 0 || betAmount > balance) {
                alert("Ungültiger Wetteinsatz!");
                return;
            }

            if (!selectedHorse) {
                alert("Bitte wählen Sie ein Pferd aus!");
                return;
            }

            balance -= betAmount;
            document.getElementById("balance").innerText = `$${balance}`;
            startRace(selectedHorse, betAmount);
        });

        function startRace(selectedHorse, betAmount) {
            const horses = document.querySelectorAll(".horse");
            horses.forEach(horse => (horse.style.left = "0px"));
            let racePositions = Array(horses.length).fill(0);
            let raceFinished = false;
            const trackWidth = document.querySelector(".race-track").offsetWidth - 100;

            let raceInterval = setInterval(() => {
                horses.forEach((horse, index) => {
                    if (!raceFinished) {
                        racePositions[index] += Math.random() * 10;
                        racePositions[index] = Math.min(racePositions[index], trackWidth);
                        horse.style.left = `${racePositions[index]}px`;

                        if (racePositions[index] >= trackWidth && !raceFinished) {
                            raceFinished = true;
                            clearInterval(raceInterval);
                            determineWinner(index + 1, selectedHorse, betAmount);
                        }
                    }
                });
            }, 50);
        }

        function determineWinner(winningHorse, selectedHorse, betAmount) {
            const result = document.getElementById("result");
            if (winningHorse === selectedHorse) {
                const winnings = betAmount * 2;
                balance += winnings;
                result.innerText = `Glückwunsch! Pferd ${winningHorse} hat gewonnen! Sie erhalten $${winnings}.`;
            } else {
                result.innerText = `Pech gehabt! Pferd ${winningHorse} hat gewonnen. Sie verlieren $${betAmount}.`;
            }
            document.getElementById("balance").innerText = `$${balance}`;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>