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

// Registrierung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    $sql = "INSERT INTO users (username, password, email) VALUES ('$user', '$pass', '$email')";
    if ($conn->query($sql) === TRUE) {
        echo "Registrierung erfolgreich! Bitte melden Sie sich an.";
    } else {
        echo "Fehler: " . $conn->error;
    }
}

// Anmeldung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            // Sitzung starten und Benutzer-ID speichern
            $_SESSION['user_id'] = $row['id'];
            header("Location: dashboard.php"); // Weiterleitung zum Dashboard
            exit();
        } else {
            echo "Falsches Passwort.";
        }
    } else {
        echo "Benutzer nicht gefunden.";
    }
}

$conn->close();
?>