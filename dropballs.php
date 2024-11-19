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
    <title>Plinko Game with Physics</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1a1a2e;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h1 {
            margin: 20px;
            color: #ffd700;
            text-shadow: 2px 2px 4px #000;
        }

        #game-container {
            position: relative;
            width: 400px;
            height: 600px;
            background: #2e2e3e;
            border-radius: 10px;
            border: 5px solid #ffffff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .peg {
            position: absolute;
            width: 12px;
            height: 12px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        }

        .ball {
            position: absolute;
            width: 20px;
            height: 20px;
            background: red;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(255, 0, 0, 0.8);
        }

        .bucket {
            position: absolute;
            bottom: 0;
            width: 66px;
            height: 60px;
            background: #ffd700;
            border-radius: 5px;
            text-align: center;
            line-height: 20px;
            font-weight: bold;
            color: #333;
            box-shadow: 0 0 5px rgba(255, 255, 0, 0.8);
        }

        .bucket span {
            display: block;
            margin-top: 5px;
            font-size: 12px;
        }

        #controls {
            margin-top: 20px;
            text-align: center;
        }

        button, input {
            padding: 10px;
            font-size: 16px;
            margin: 5px;
            border: none;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        input[type="number"] {
            width: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Plinko Game with Physics</h1>
    <div id="score-container">
        <p>Guthaben: <span id="balance">100</span></p>
    </div>
    <div id="controls">
        <label for="ball-count">Bälle:</label>
        <input type="number" id="ball-count" min="1" max="10" value="1">
        <button id="play-button">Spielen</button>
    </div>
    <div id="game-container"></div>

    <script>
        const gameContainer = document.getElementById('game-container');
        const balanceElement = document.getElementById('balance');
        const playButton = document.getElementById('play-button');
        const ballCountInput = document.getElementById('ball-count');
        let balance = 100;

        const gravity = 0.1; // Gravity effect
        const damping = 0.9; // Energy loss on collisions

        // Generate pegs
        function generatePegs() {
            for (let y = 50; y < 500; y += 50) {
                for (let x = (y % 100 === 0 ? 33 : 0); x < 400; x += 66) {
                    const peg = document.createElement('div');
                    peg.classList.add('peg');
                    peg.style.left = `${x}px`;
                    peg.style.top = `${y}px`;
                    gameContainer.appendChild(peg);
                }
            }
        }

        // Generate buckets
        function generateBuckets() {
            for (let i = 0; i < 6; i++) {
                const bucket = document.createElement('div');
                bucket.classList.add('bucket');
                bucket.style.left = `${i * 66}px`;
                const score = Math.floor(Math.random() * 50) + 10;
                bucket.innerHTML = `${score}<span>Gewinn</span>`;
                bucket.setAttribute('data-score', score);
                gameContainer.appendChild(bucket);
            }
        }

        // Drop ball with realistic physics
        function dropBall() {
            const ball = document.createElement('div');
            ball.classList.add('ball');
            ball.style.left = `${Math.random() * 380}px`;
            ball.style.top = `0px`;
            gameContainer.appendChild(ball);

            let posX = parseFloat(ball.style.left);
            let posY = 0;
            let velocityX = (Math.random() - 0.5) * 2;
            let velocityY = 0;

            const interval = setInterval(() => {
                // Apply gravity
                velocityY += gravity;

                // Update ball position
                posX += velocityX;
                posY += velocityY;

                // Check collisions with pegs
                const pegs = document.querySelectorAll('.peg');
                pegs.forEach(peg => {
                    const pegRect = peg.getBoundingClientRect();
                    const ballRect = ball.getBoundingClientRect();

                    if (
                        ballRect.left < pegRect.right &&
                        ballRect.right > pegRect.left &&
                        ballRect.top < pegRect.bottom &&
                        ballRect.bottom > pegRect.top
                    ) {
                        // Simple collision response
                        velocityY = -velocityY * damping;
                        velocityX += (Math.random() - 0.5) * 0.5; // Add slight randomness
                    }
                });

                // Check for container boundaries
                if (posX < 0 || posX > 380) {
                    velocityX = -velocityX * damping;
                }

                ball.style.left = `${posX}px`;
                ball.style.top = `${posY}px`;

                // Check if the ball reaches the bottom
                if (posY > 540) {
                    clearInterval(interval);
                    checkBucketCollision(ball);
                }
            }, 16);
        }

        // Check which bucket the ball lands in
        function checkBucketCollision(ball) {
            const ballRect = ball.getBoundingClientRect();
            const buckets = document.querySelectorAll('.bucket');

            buckets.forEach(bucket => {
                const bucketRect = bucket.getBoundingClientRect();
                if (
                    ballRect.bottom > bucketRect.top &&
                    ballRect.left > bucketRect.left &&
                    ballRect.right < bucketRect.right
                ) {
                    const score = parseInt(bucket.getAttribute('data-score'));
                    balance += score;
                    balanceElement.textContent = balance;
                }
            });

            ball.remove();
        }

        // Initialize game
        function initGame() {
            gameContainer.innerHTML = ''; // Clear the container
            generatePegs();
            generateBuckets();
        }

        playButton.addEventListener('click', () => {
            const ballCount = parseInt(ballCountInput.value);

            if (balance >= ballCount * 10) {
                balance -= ballCount * 10;
                balanceElement.textContent = balance;

                for (let i = 0; i < ballCount; i++) {
                    setTimeout(() => {
                        dropBall();
                    }, i * 500);
                }
            } else {
                alert('Nicht genug Guthaben!');
            }
        });

        initGame();
    </script>
</body>
</html>
<?php
$conn->close();
?>