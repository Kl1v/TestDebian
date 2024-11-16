<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einträge aus der Datenbank</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Einträge aus der Tabelle</h1>
        <?php
        $host = 'localhost';
        $dbname = 'test';
        $username = 'kremsguesser';
        $password = '123mysql';

        try {
            // Verbindung zur Datenbank herstellen
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Daten aus der Tabelle abrufen
            $stmt = $pdo->query("SELECT ID, Text FROM Einträge");
            $einträge = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Überprüfen, ob es Einträge gibt
            if (count($einträge) > 0): ?>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Text</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($einträge as $eintrag): ?>
                <tr>
                    <td><?= htmlspecialchars($eintrag['ID']) ?></td>
                    <td><?= htmlspecialchars($eintrag['Text']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="alert alert-info text-center">Keine Einträge in der Tabelle gefunden.</div>
        <?php endif;

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Fehler: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>