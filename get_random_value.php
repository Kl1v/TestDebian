<?php
session_start(); // Session starten

// Den gespeicherten Wert aus der Session holen
$random_value = isset($_SESSION['random_value']) ? $_SESSION['random_value'] : null;

if ($random_value !== null) {
    echo $random_value; // Gebe den Wert zurück
} else {
    echo 'Wert nicht verfügbar';
}
?>