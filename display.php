<?php
session_start(); // Session starten

// Den gespeicherten Wert aus der Session holen
$random_value = isset($_SESSION['random_value']) ? $_SESSION['random_value'] : 'Wert nicht verfügbar';

// Überprüfen, ob das Passwort korrekt eingegeben wurde
$accessGranted = isset($_POST['password']) && $_POST['password'] === 'lolhtl';
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Richtiger Wert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container text-center mt-5">
        <?php if ($accessGranted): ?>
        <h1>Richtiger Wert: <span id="randomValue"><?php echo $random_value; ?></span></h1>
        <a href="index.php" class="btn btn-primary mt-3">Zurück zum Spiel</a>
        <?php else: ?>
        <h1>Passwort eingeben</h1>
        <form method="post" class="mt-3">
            <input type="password" name="password" class="form-control" placeholder="Passwort" required>
            <button type="submit" class="btn btn-primary mt-3">Zugreifen</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if ($accessGranted): ?>
    <script>
    function fetchRandomValue() {
        $.ajax({
            url: 'get_random_value.php', // Die Datei, die den Wert zurückgibt
            method: 'GET',
            success: function(data) {
                $('#randomValue').text(data); // Den Wert in das span-Element einfügen
            },
            error: function() {
                $('#randomValue').text('Fehler beim Abrufen des Wertes');
            }
        });
    }

    // Alle 2 Sekunden den Wert aktualisieren
    setInterval(fetchRandomValue, 1000);
    </script>
    <?php endif; ?>
</body>

</html>