<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pferderennen</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Live Pferderennen</h1>
    <div id="countdown">Lädt...</div>
    <div id="betting">
        <h2>Setze auf ein Pferd</h2>
        <form id="bet-form">
            <label for="horse">Pferd:</label>
            <select id="horse" name="horse">
                <option value="1">Pferd 1</option>
                <option value="2">Pferd 2</option>
                <option value="3">Pferd 3</option>
                <!-- Bis Pferd 8 -->
            </select>
            <label for="amount">Betrag:</label>
            <input type="number" id="amount" name="amount" required>
            <button type="submit">Wette platzieren</button>
        </form>
    </div>
    <script>
        function fetchRace() {
            $.get('race.php', function(data) {
                const response = JSON.parse(data);
                if (response.status === 'new_race') {
                    updateCountdown(new Date(response.time).getTime());
                } else if (response.status === 'ongoing') {
                    updateCountdown(new Date(response.time).getTime());
                }
            });
        }

        function updateCountdown(endTime) {
            const timer = setInterval(() => {
                const now = new Date().getTime();
                const distance = endTime - now;
                if (distance < 0) {
                    clearInterval(timer);
                    document.getElementById("countdown").innerHTML = "Rennen läuft!";
                    fetchRace(); // Neues Rennen abrufen
                } else {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    document.getElementById("countdown").innerHTML = `${minutes}m ${seconds}s`;
                }
            }, 1000);
        }

        $(document).ready(function() {
            fetchRace();
        });
    </script>
</body>
</html>
