<?php
require 'db.php';

// Starte neues Rennen, wenn nötig
function startNewRace($pdo) {
    $nextRaceTime = new DateTime();
    $nextRaceTime->modify('+5 minutes');
    $stmt = $pdo->prepare("INSERT INTO horse_races (race_time) VALUES (:race_time)");
    $stmt->execute(['race_time' => $nextRaceTime->format('Y-m-d H:i:s')]);
    return $pdo->lastInsertId();
}

// Quoten für Pferde generieren
function generateOdds() {
    $odds = [];
    for ($i = 0; $i < 8; $i++) {
        $odds[] = round(mt_rand(150, 1000) / 100, 2); // 1.50 bis 10.00
    }
    return $odds;
}

// Gewinner ermitteln
function pickWinner($odds) {
    $totalWeight = array_sum(array_map(fn($odd) => 1 / $odd, $odds));
    $random = mt_rand(0, $totalWeight * 1000) / 1000;
    $cumulative = 0;

    foreach ($odds as $index => $odd) {
        $cumulative += 1 / $odd;
        if ($random <= $cumulative) {
            return $index + 1;
        }
    }
    return 1; // Fallback
}

// Rennen auswerten
function evaluateRace($pdo, $raceId) {
    $stmt = $pdo->prepare("SELECT * FROM bets WHERE race_id = :race_id AND result = 'pending'");
    $stmt->execute(['race_id' => $raceId]);
    $bets = $stmt->fetchAll();

    $odds = generateOdds();
    $winner = pickWinner($odds);

    foreach ($bets as $bet) {
        $result = $bet['bet_choice'] == $winner ? 'win' : 'loss';
        $stmt = $pdo->prepare("UPDATE bets SET result = :result WHERE id = :id");
        $stmt->execute(['result' => $result, 'id' => $bet['id']]);

        if ($result === 'win') {
            $payout = $bet['amount'] * $odds[$winner - 1];
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + :payout WHERE id = :user_id");
            $stmt->execute(['payout' => $payout, 'user_id' => $bet['user_id']]);
        }
    }

    // Ergebnis speichern
    $stmt = $pdo->prepare("UPDATE horse_races SET result = :result WHERE id = :id");
    $stmt->execute(['result' => json_encode(['winner' => $winner, 'odds' => $odds]), 'id' => $raceId]);
}

// Aktuelles Rennen abrufen oder neues starten
$stmt = $pdo->prepare("SELECT * FROM horse_races WHERE race_time > NOW() ORDER BY race_time ASC LIMIT 1");
$stmt->execute();
$currentRace = $stmt->fetch();

if (!$currentRace) {
    $raceId = startNewRace($pdo);
    echo json_encode(['race_id' => $raceId, 'status' => 'new_race']);
} else {
    echo json_encode(['race_id' => $currentRace['id'], 'status' => 'ongoing', 'time' => $currentRace['race_time']]);
}
?>
