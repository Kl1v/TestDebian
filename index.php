<script>
function fetchRaceData() {
    fetch('race_manager.php')
        .then(response => response.json())
        .then(data => {
            const odds = data.odds;
            const raceTime = new Date(data.race_time);

            // Gewinnquoten anzeigen
            let oddsHtml = '';
            for (const horse in odds) {
                oddsHtml += `<p>${horse}: ${odds[horse]}x</p>`;
            }
            document.getElementById('race-info').innerHTML = `
                <h3>Gewinnquoten</h3>
                ${oddsHtml}
                <p>Nächstes Rennen um: ${raceTime.toLocaleTimeString()}</p>
            `;

            updateCountdown(raceTime.getTime());
        });
}

function updateCountdown(endTime) {
    const timer = setInterval(() => {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            clearInterval(timer);
            document.getElementById("race-info").innerHTML = "Rennen läuft!";
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        document.getElementById("race-info").innerHTML += `<p>Countdown: ${minutes}m ${seconds}s</p>`;
    }, 1000);
}

fetchRaceData();
</script>
