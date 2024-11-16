<?php
session_start(); // Session starten
include 'connection.php';

// Zufälligen Wert generieren und in der Session speichern
$random = rand(0, 1000); // Generiere einen zufälligen Wert zwischen 0 und 1000
$_SESSION['random_value'] = $random; // Speichern des Wertes in der Session

// Prüfen, ob das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $score = $_POST['score'];

    // SQL-Statement vorbereiten
    $stmt = $conn->prepare("INSERT INTO scoreboard (name, score) VALUES (?, ?)");
    $stmt->bind_param("sd", $name, $score);

    // SQL ausführen und Fehler prüfen
    if ($stmt->execute()) {
        echo "<div class='alert alert-success text-center'>Score erfolgreich gespeichert!</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    
    // Weiterleitung auf dieselbe Seite, um das erneute Senden des Formulars zu vermeiden
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag der offenen Tür - HTL Krems</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="container">
        <div class="logo text-center">
            <img src="htlkrems-logo.png" alt="HTL Krems Logo" style="width: 200px; height: auto;" class="mb-4">
        </div>
        <h1 class="text-center">OPEN DAYS SPIEL</h1>
        <div class="row">
            <div class="mb-5 text-center">
                <h5>Ein zufälliger Wert zwischen 0 und 1000 wird generiert. </h5>
                <h5 class="text-primary">Errate den Wert des Punktes auf dem Slider!</h5>
            </div>
            <div class="col-lg-12 text-center">
                <input type="range" class="container form-range" min="0" max="1000" id="customRange1" disabled>
            </div>
            <div class="col-lg-6">0</div>
            <div class="col-lg-6 text-end">1000</div>
        </div>
    </header>
    <div class="text-center">
        <input type="number" id="userGuess" class="mb-4 form-control" placeholder="Gib deinen Wert ein" min="0" max="1000" style="width: 30%; margin: 0 auto;"><br>
        <button type="button" class="btn btn-primary" style="width: 7%;" data-bs-toggle="modal" data-bs-target="#exampleModal"
            id="guessButton" disabled>
            Guess
        </button>

        <form method="post" action="" class="modal fade" id="exampleModal" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel" style="color: black;">Dein Guess</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Dein Wert: <span id="userValue"></span></p>
                        <p>Richtiger Wert: <span id="correctValue"></span></p>
                        <p>_________________________</p>
                        <p>Dein Score: <span id="score"></span></p>
                        <div class="mt-3">
                            <label for="userName" class="form-label">Dein Name:</label>
                            <input type="text" name="name" id="userName" class="form-control"
                                placeholder="Gib deinen Namen ein" required>
                            <input type="hidden" name="score" id="scoreInput">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary reload-page"
                            data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Absenden</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
<script>
    $(document).ready(function() {
        var random = <?php echo $random; ?>;
        document.getElementById("customRange1").value = random;

        $('#userGuess').on('input', function() {
            var userGuess = $('#userGuess').val();
            if (userGuess !== "" && !isNaN(userGuess) && userGuess >= 0 && userGuess <= 1000) {
                $('#guessButton').prop('disabled', false);
            } else {
                $('#guessButton').prop('disabled', true);
            }
        });

        $('#guessButton').click(function() {
            var userGuess = parseInt($('#userGuess').val());
            var x = Math.abs(5 * (random - userGuess));
            var score = Math.max(0, Math.min(1000, 1000 - x));
            score = score / 10;

            $('#userValue').text(userGuess);
            $('#correctValue').text(random);
            $('#score').text(score);
            $('#scoreInput').val(score);
        });

        // Enter-Taste als Ersatz für Guess-Button
        $('#userGuess').keypress(function(event) {
            if (event.which == 13 && !$('#guessButton').prop('disabled')) {
                event.preventDefault();
                $('#guessButton').click();
            }
        });

        $('#exampleModal').on('hidden.bs.modal', function() {
            location.reload();
        });

        $('.reload-page').click(function() {
            location.reload();
        });
    });
</script>

</body>
</html>
