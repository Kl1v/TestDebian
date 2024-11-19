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
    <title>Blackjack Casino</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: radial-gradient(circle, #006400, #013220);
            color: white;
            text-align: center;
        }

        .table {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            border-radius: 15px;
            background: #0f3d0f;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.8);
            position: relative;
        }

        .chip-display, .message {
            margin-bottom: 20px;
        }

        .chip-display span {
            font-size: 24px;
            font-weight: bold;
        }

        .cards-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .card {
            width: 80px;
            height: 120px;
            border-radius: 10px;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            color: black;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }

        .card.suit-diamonds, .card.suit-hearts {
            color: red;
        }

        .hidden {
            background: url('https://via.placeholder.com/80x120') center center / cover;
            color: transparent;
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

        .hidden-card {
            opacity: 0;
        }

        .controls input {
            width: 80px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="table">
        <div class="chip-display">
            <span>Balance:</span> $<span id="balance">1000</span>
        </div>

        <div class="dealer">
            <h2>Dealer</h2>
            <div class="cards-container" id="dealer-cards">
                <!-- Dealer cards -->
            </div>
            <div id="dealer-score">Score: 0</div>
        </div>

        <hr style="border: 1px solid white; margin: 20px 0;">

        <div class="player">
            <h2>Player</h2>
            <div class="cards-container" id="player-cards">
                <!-- Player cards -->
            </div>
            <div id="player-score">Score: 0</div>
        </div>

        <div class="message" id="message"></div>

        <div class="controls">
            <input type="number" id="bet" placeholder="Bet" min="1" max="1000" value="100">
            <button id="btn-start">Start Game</button>
            <button id="btn-hit" disabled>Hit</button>
            <button id="btn-stand" disabled>Stand</button>
            <button id="btn-restart" style="display: none;">Play Again</button>
        </div>
    </div>

    <script>
        const suits = ["hearts", "diamonds", "clubs", "spades"];
        const ranks = ["2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K", "A"];
        let deck = [];
        let playerCards = [];
        let dealerCards = [];
        let playerBalance = 1000;
        let playerBet = 0;
        let message = "";
        let gameOver = false;

        const balanceElement = document.getElementById("balance");
        const playerCardsElement = document.getElementById("player-cards");
        const dealerCardsElement = document.getElementById("dealer-cards");
        const messageElement = document.getElementById("message");
        const playerScoreElement = document.getElementById("player-score");
        const dealerScoreElement = document.getElementById("dealer-score");
        const btnStart = document.getElementById("btn-start");
        const btnHit = document.getElementById("btn-hit");
        const btnStand = document.getElementById("btn-stand");
        const btnRestart = document.getElementById("btn-restart");
        const betInput = document.getElementById("bet");

        function createDeck() {
            deck = [];
            for (let suit of suits) {
                for (let rank of ranks) {
                    deck.push({ suit, rank });
                }
            }
            shuffle(deck);
        }

        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        function calculateScore(cards) {
            let score = 0;
            let aces = 0;
            for (let card of cards) {
                if (card.rank === "A") {
                    score += 11;
                    aces++;
                } else if (["K", "Q", "J"].includes(card.rank)) {
                    score += 10;
                } else {
                    score += parseInt(card.rank);
                }
            }
            while (score > 21 && aces) {
                score -= 10;
                aces--;
            }
            return score;
        }

        function renderCard(card, hidden = false) {
            const cardDiv = document.createElement("div");
            cardDiv.classList.add("card", `suit-${card.suit}`);
            if (hidden) {
                cardDiv.classList.add("hidden");
            } else {
                cardDiv.innerHTML = `${card.rank} <br>&#${card.suit === "hearts" ? "9829" : card.suit === "diamonds" ? "9830" : card.suit === "clubs" ? "9827" : "9824"};`;
            }
            return cardDiv;
        }

        function renderGame() {
            playerCardsElement.innerHTML = "";
            dealerCardsElement.innerHTML = "";
            for (let card of playerCards) {
                playerCardsElement.appendChild(renderCard(card));
            }
            for (let i = 0; i < dealerCards.length; i++) {
                const hidden = i === 0 && !gameOver;
                dealerCardsElement.appendChild(renderCard(dealerCards[i], hidden));
            }
            playerScoreElement.textContent = `Score: ${calculateScore(playerCards)}`;
            dealerScoreElement.textContent = `Score: ${gameOver ? calculateScore(dealerCards) : "?"}`;
            balanceElement.textContent = playerBalance;
            messageElement.textContent = message;
        }

        function startGame() {
            playerBet = parseInt(betInput.value);
            if (playerBet > playerBalance || playerBet <= 0) {
                message = "Invalid bet!";
                renderGame();
                return;
            }
            createDeck();
            playerCards = [deck.pop(), deck.pop()];
            dealerCards = [deck.pop(), deck.pop()];
            message = "Make your move!";
            gameOver = false;
            btnStart.disabled = true;
            btnHit.disabled = false;
            btnStand.disabled = false;
            betInput.disabled = true;
            renderGame();
        }

        function hit() {
            if (gameOver) return;
            playerCards.push(deck.pop());
            const playerScore = calculateScore(playerCards);
            if (playerScore > 21) {
                message = "You busted! Dealer wins.";
                playerBalance -= playerBet;
                gameOver = true;
                endGame();
            }
            renderGame();
        }

        function stand() {
            if (gameOver) return;
            let dealerScore = calculateScore(dealerCards);
            while (dealerScore < 17) {
                dealerCards.push(deck.pop());
                dealerScore = calculateScore(dealerCards);
            }
            const playerScore = calculateScore(playerCards);
            if (dealerScore > 21 || playerScore > dealerScore) {
                message = "You win!";
                playerBalance += playerBet;
            } else if (dealerScore === playerScore) {
                message = "It's a draw!";
            } else {
                message = "Dealer wins.";
                playerBalance -= playerBet;
            }
            gameOver = true;
            endGame();
        }

        function endGame() {
            renderGame();
            btnRestart.style.display = "block";
            btnHit.disabled = true;
            btnStand.disabled = true;
        }

        function restartGame() {
            btnStart.disabled = false;
            betInput.disabled = false;
            btnRestart.style.display = "none";
            playerCards = [];
            dealerCards = [];
            message = "";
            renderGame();
        }

        btnStart.addEventListener("click", startGame);
        btnHit.addEventListener("click", hit);
        btnStand.addEventListener("click", stand);
        btnRestart.addEventListener("click", restartGame);

        renderGame();
    </script>
</body>
</html>
<?php
$conn->close();
?>