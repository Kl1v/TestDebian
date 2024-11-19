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
    <title>Slot Machine - Book of Ra Style</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background: #222;
            color: white;
        }

        .slot-machine {
            display: flex;
            justify-content: center;
            margin: 50px auto;
            gap: 10px;
        }

        .reel-container {
            width: 100px;
            height: 200px;
            overflow: hidden;
            border: 3px solid #fff;
            border-radius: 10px;
            background: black;
        }

        .reel {
            display: flex;
            flex-direction: column;
            position: relative;
            animation: none;
        }

        .symbol {
            width: 100%;
            height: 50px;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #333;
        }

        #spinButton {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1rem;
            background: #ffcc00;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        #spinButton:hover {
            background: #ffaa00;
        }

        .info {
            margin-top: 20px;
            font-size: 1.2rem;
        }

        .highlight {
            color: #ffd700;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="slot-machine">
        <div class="reel-container">
            <div class="reel" id="reel1"></div>
        </div>
        <div class="reel-container">
            <div class="reel" id="reel2"></div>
        </div>
        <div class="reel-container">
            <div class="reel" id="reel3"></div>
        </div>
        <div class="reel-container">
            <div class="reel" id="reel4"></div>
        </div>
        <div class="reel-container">
            <div class="reel" id="reel5"></div>
        </div>
    </div>
    <button id="spinButton">Spin</button>
    <div class="info">
        <div>Guthaben: <span class="highlight" id="balance">1000</span></div>
        <div>Freispiele: <span class="highlight" id="freeSpins">0</span></div>
        <div id="result"></div>
    </div>

    <script>
        const symbols = ["🔷", "👑", "🦅", "💎", "📜", "📘"]; // Book-Symbol ist "📘"
        const payouts = {
            "🔷": 5,
            "👑": 10,
            "🦅": 15,
            "💎": 20,
            "📜": 50,
            "📘": 100 // Spezialgewinn für Bücher
        };

        const reels = [
            document.getElementById("reel1"),
            document.getElementById("reel2"),
            document.getElementById("reel3"),
            document.getElementById("reel4"),
            document.getElementById("reel5")
        ];

        const spinButton = document.getElementById("spinButton");
        const balanceDisplay = document.getElementById("balance");
        const freeSpinsDisplay = document.getElementById("freeSpins");
        const resultDisplay = document.getElementById("result");

        let balance = 1000;
        let freeSpins = 0;

        function initializeReels() {
            reels.forEach(reel => {
                for (let i = 0; i < 20; i++) {
                    const symbol = document.createElement("div");
                    symbol.className = "symbol";
                    symbol.textContent = symbols[Math.floor(Math.random() * symbols.length)];
                    reel.appendChild(symbol);
                }
            });
        }

        function spin() {
            if (balance <= 0 && freeSpins === 0) {
                resultDisplay.textContent = "Nicht genug Guthaben!";
                return;
            }

            let cost = 10;
            if (freeSpins > 0) {
                cost = 0;
                freeSpins--;
            } else {
                balance -= 10;
            }

            updateDisplay();
            spinButton.disabled = true;
            resultDisplay.textContent = "";

            reels.forEach((reel, index) => {
                const duration = 2 + index * 0.5; // Unterschiedliche Geschwindigkeiten
                reel.style.animation = `spin ${duration}s cubic-bezier(0.25, 0.1, 0.25, 1)`;

                setTimeout(() => {
                    reel.style.animation = "none";
                    const symbols = Array.from(reel.children);
                    const randomOffset = Math.floor(Math.random() * symbols.length);

                    reel.innerHTML = "";
                    for (let i = 0; i < symbols.length; i++) {
                        const symbol = symbols[(i + randomOffset) % symbols.length].cloneNode(true);
                        reel.appendChild(symbol);
                    }
                }, duration * 1000);
            });

            setTimeout(() => {
                calculateResult();
                spinButton.disabled = false;
            }, 4000);
        }

        function calculateResult() {
            const visibleSymbols = reels.map(reel => reel.children[1].textContent);
            const bookCount = visibleSymbols.filter(symbol => symbol === "📘").length;

            if (bookCount >= 3) {
                freeSpins += 10;
                resultDisplay.textContent = "🎉 3+ Bücher! 10 Freispiele gewonnen! 🎉";
            } else {
                let win = 0;
                const counts = {};
                visibleSymbols.forEach(symbol => {
                    counts[symbol] = (counts[symbol] || 0) + 1;
                });

                Object.keys(counts).forEach(symbol => {
                    if (counts[symbol] >= 3) {
                        win += payouts[symbol] * counts[symbol];
                    }
                });

                if (win > 0) {
                    balance += win;
                    resultDisplay.textContent = `🎉 Du hast ${win} gewonnen! 🎉`;
                } else {
                    resultDisplay.textContent = "Kein Gewinn. Versuche es erneut!";
                }
            }

            updateDisplay();
        }

        function updateDisplay() {
            balanceDisplay.textContent = balance;
            freeSpinsDisplay.textContent = freeSpins;
        }

        // CSS Animation Keyframes erstellen
        const style = document.createElement("style");
        style.textContent = `
            @keyframes spin {
                0% { transform: translateY(0); }
                100% { transform: translateY(-100%); }
            }
        `;
        document.head.appendChild(style);

        initializeReels();
        spinButton.addEventListener("click", spin);
    </script>
</body>
</html>
<?php
$conn->close();
?>