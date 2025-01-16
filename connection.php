<?php
$servername = "kremsguesser.duckdns.org"; // Serveradresse
$username = "kremsguesser";              // Benutzername
$password = "123mysql";                  // Passwort
$dbname = "tdot";                        // Datenbankname
$port = 3306;                            // MySQL-Standardport

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verbindung auf Fehler prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

?>
