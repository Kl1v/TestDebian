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
    <title>Roulette Chip Flip</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: radial-gradient(circle, #222, #111);
            color: white;
            text-align: center;
        }

        .table {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            border-radius: 15px;
            background: #333;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.8);
            position: relative;
        }

        .chip {
            width: 100px;
            height: 100px;
            margin: 30px auto;
            position: relative;
            border-radius: 50%;
            perspective: 1000px;
        }

        .chip-inner {
            width: 100%;
            height: 100%;
            position: absolute;
            border-radius: 50%;
            transform-style: preserve-3d;
            animation: none;
            transition: transform 1s ease-in-out;
        }

        .chip-front, .chip-back {
            width: 100%;
            height: 100%;
            position: absolute;
            backface-visibility: hidden;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .chip-front {
            background: red;
        }

        .chip-back {
            background: black;
            transform: rotateY(180deg);
        }

        .chip:hover {
            cursor: pointer;
        }

        .controls {
            margin-top: 20px;
        }

        .controls button, .controls input {
            padding: 10px 20px;
            margin: 10px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .controls button:hover {
            background: #ffd700;
            color: black;
        }

        .message, .balance {
            margin-top: 20px;
            font-size: 24px;
        }

        .hidden {
            display: none;
        }

        @keyframes multiFlip {
            0% {
                transform: rotateY(0deg);
            }
            25% {
                transform: rotateY(180deg);
            }
            50% {
                transform: rotateY(360deg);
            }
            75% {
                transform: rotateY(540deg);
            }
            100% {
                transform: rotateY(720deg);
            }
        }
    </style>
</head>
<body>
    <div class="table">
        <div class="chip">
            <div class="chip-inner" id="chip">
                <div class="chip-front">RED</div>
                <div class="chip-back">BLACK</div>
            </div>
        </div>

        <div class="balance">
            <span>Balance:</span> $<span id="balance">1000</span>
        </div>

        <div class="controls">
            <input type="number" id="bet" placeholder="Bet" min="1" max="1000" value="100">
            <button id="bet-red">Bet on Red</button>
            <button id="bet-black">Bet on Black</button>
            <button id="restart" class="hidden">Play Again</button>
        </div>

        <div class="message" id="message"></div>
    </div>

    <script>
        const chip = document.getElementById("chip");
        const balanceElement = document.getElementById("balance");
        const messageElement = document.getElementById("message");
        const betInput = document.getElementById("bet");
        const betRedButton = document.getElementById("bet-red");
        const betBlackButton = document.getElementById("bet-black");
        const restartButton = document.getElementById("restart");

        let playerBalance = 1000;
        let playerBet = 0;
        let playerChoice = null;
        let isFlipping = false;

        function flipChip() {
            return new Promise((resolve) => {
                const outcome = Math.random() < 0.5 ? "red" : "black";
                // Add the animation class
                chip.style.animation = "multiFlip 2s ease-in-out";
                setTimeout(() => {
                    // Decide the final transform based on the outcome
                    chip.style.transform = outcome === "red" ? "rotateY(720deg)" : "rotateY(900deg)";
                    chip.style.animation = "none";
                    resolve(outcome);
                }, 2000); // Match the animation duration
            });
        }

        function resetGame() {
            playerChoice = null;
            isFlipping = false;
            messageElement.textContent = "";
            restartButton.classList.add("hidden");
            betRedButton.disabled = false;
            betBlackButton.disabled = false;
            chip.style.transform = "rotateY(0deg)";
        }

        async function handleBet(choice) {
            if (isFlipping) return;

            playerBet = parseInt(betInput.value);
            if (playerBet > playerBalance || playerBet <= 0) {
                messageElement.textContent = "Invalid bet!";
                return;
            }

            playerChoice = choice;
            isFlipping = true;
            messageElement.textContent = "Flipping the chip...";
            betRedButton.disabled = true;
            betBlackButton.disabled = true;

            const outcome = await flipChip();
            if (outcome === playerChoice) {
                playerBalance += playerBet;
                messageElement.textContent = `You win! The chip landed on ${outcome.toUpperCase()}.`;
            } else {
                playerBalance -= playerBet;
                messageElement.textContent = `You lose! The chip landed on ${outcome.toUpperCase()}.`;
            }

            balanceElement.textContent = playerBalance;
            restartButton.classList.remove("hidden");
        }

        betRedButton.addEventListener("click", () => handleBet("red"));
        betBlackButton.addEventListener("click", () => handleBet("black"));
        restartButton.addEventListener("click", resetGame);

        resetGame();
    </script>
</body>
</html>
<?php
$conn->close();
?>