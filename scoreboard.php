<?php
include 'connection.php'; 

// Abrufen der Punktestände
function fetchScores($conn) {
    $sql = "SELECT name, score FROM scoreboard ORDER BY score DESC";
    $result = $conn->query($sql);

    $scores = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
    }

    return $scores;
}

// Überprüfen, ob eine POST-Anfrage zum Abrufen der Punktestände gesendet wurde
if (isset($_POST['fetch'])) {
    $scores = fetchScores($conn);
    $conn->close();
    echo json_encode($scores); // Geben Sie die Scores als JSON zurück
    exit; // Stoppen Sie die weitere Verarbeitung
}

// Standardmäßig die Punktestände abrufen, um sie beim Laden der Seite anzuzeigen
$scores = fetchScores($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard - HTL Krems</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .container {
        margin-top: 20px;
    }

    h1 {
        margin-bottom: 20px;
    }

    /* Stil für die Top 3 Plätze */
    .top-1 {
        font-size: 2.5em;
        font-weight: bold;
        color: #000;
    }

    .top-2 {
        font-size: 2.0em;
        font-weight: bold;
        color: #000;
    }

    .top-3 {
        font-size: 1.7em;
        font-weight: bold;
        color: #000;
    }

    .medal {
        margin-right: 10px;
    }

    .size {
        font-size: 1.2em;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Scoreboard</h1>
        <table class="table table-striped" id="scoreTable">
            <thead>
                <tr>
                    <th scope="col">Platz</th>
                    <th scope="col">Name</th>
                    <th scope="col">Punkte</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($scores as $index => $player) {
                    $class = 'size ';
                    $medal = '';

                    if ($index == 0) {
                        $class = 'top-1 table-warning';
                        $medal = '🥇';
                    } elseif ($index == 1) {
                        $class = 'top-2 table-secondary';
                        $medal = '🥈';
                    } elseif ($index == 2) {
                        $class = 'top-3 table-danger';
                        $medal = '🥉';
                    }

                    echo "<tr class='{$class}'>";
                    echo "<th scope='row'>" . ($index + 1) . " {$medal}</th>";
                    echo "<td>" . htmlspecialchars($player['name']) . "</td>";

                    // Formatierung des Scores: nur Nachkommastelle anzeigen, wenn nicht .0
                    $score = $player['score'];
                    $displayScore = (floor($score) == $score) ? (int)$score : $score;
                    echo "<td>" . htmlspecialchars($displayScore) . "</td>";

                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function fetchScores() {
        $.ajax({
            url: '', // aktuelle Datei
            type: 'POST',
            data: {
                fetch: true
            },
            success: function(data) {
                const scores = JSON.parse(data);
                updateTable(scores);
            },
            error: function(xhr, status, error) {
                console.error("Fehler beim Abrufen der Punktestände:", error);
            }
        });
    }

    function updateTable(scores) {
        const tableBody = $('#scoreTable tbody');
        tableBody.empty(); // Bestehende Zeilen leeren

        scores.forEach((player, index) => {
            let rowClass = 'size ';
            let medal = '';

            if (index === 0) {
                rowClass = 'top-1 table-warning';
                medal = '🥇';
            } else if (index === 1) {
                rowClass = 'top-2 table-secondary';
                medal = '🥈';
            } else if (index === 2) {
                rowClass = 'top-3 table-danger';
                medal = '🥉';
            }

            // Formatierung des Scores: nur Nachkommastelle anzeigen, wenn nicht .0
            const displayScore = (Math.floor(player.score) == player.score) ? parseInt(player.score) : player.score;


            tableBody.append(`
                <tr class="${rowClass}">
                    <th scope='row'>${index + 1} ${medal}</th>
                    <td>${player.name}</td>
                    <td>${displayScore}</td>
                </tr>
            `);
        });
    }

    setInterval(fetchScores, 1000); // Live-Aktualisierung alle 1 Sekunde
    </script>
</body>

</html>