<?php
$host = '193.154.207.221';    // IP-Adresse oder Domain des MariaDB-Servers
$dbname = 'test';       // Der Name der Datenbank
$username = 'kremsguesser';  // Der Benutzername
$password = '123mysql';      // Das Passwort

try {
    // PDO-Verbindung aufbauen
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";  // DSN-String für MySQL/MariaDB
    $pdo = new PDO($dsn, $username, $password);

    // Setze den PDO-Fehlermodus auf Exception, damit Fehler besser gehandhabt werden
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Verbindung erfolgreich!";
} catch (PDOException $e) {
    // Fehlerbehandlung
    echo "Verbindung fehlgeschlagen: " . $e->getMessage();
}
?>