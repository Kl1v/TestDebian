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
    <title>Casino Games</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a1a);
            color: white;
            text-align: center;
        }

        h1 {
            font-size: 48px;
            margin-top: 50px;
            color: #ffd700;
        }

        .menu {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh;
        }

        .menu button {
            padding: 15px 30px;
            margin: 10px;
            font-size: 24px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            background: #444;
            color: #ffd700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu button:hover {
            background: #ffd700;
            color: black;
            transform: scale(1.1);
        }

        .menu a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Welcome to the Casino</h1>
    <div class="menu">
        <a href="slot.html"><button>🎰 Slot Machine</button></a>
        <a href="bj.html"><button>♠️ BlackJack</button></a>
        <a href="flip.html"><button>🪙 Coin Flip</button></a>
        <a href="dropballs.html"><button>🔵 Drop Balls</button></a>
    </div>
</body>
</html>
<?php
$conn->close();
?>