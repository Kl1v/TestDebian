<?php
$servername = "194.155.88.76";
$username = "kremsguesser";
$password = "123mysql"; // Ersetze xxx mit deinem Passwort
$dbname = "tdot";

// Pfad zum SSL-Zertifikat

// Initialisiere MySQLi
$conn = mysqli_init();

// SSL-Einstellungen setzen

// Verbinde ohne Zertifikatsprüfung
if (!mysqli_real_connect($conn, $servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT)) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}
?>