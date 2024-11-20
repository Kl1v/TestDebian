<?php
require 'db.php';

// Starte neues Rennen, wenn keins läuft
function startNewRace($pdo) {
    $nextRaceTime = new DateTime();
    $nextRaceTime->modify('+5 minutes');

    // Gewinnquoten für 4 Pferde generieren
    $odds = [
        "Horse A" => round(mt_rand(150, 250) / 100, 2), // 1.5 bis 2.5
        "Horse B" => round(mt_rand(200, 400) / 100, 2), // 2.0 bis 4.0
        "Horse C" => round(mt_rand(300, 800) / 100, 2), // 3.0 bis 8.0
        "Horse D" => round(mt_rand(500, 1000) / 100, 2) // 5.0 bis 10.0
    ];

    $stmt = $pdo->prepare("INSERT INTO horse_races (race_time, odds) VALUES (:race_time, :odds)");
    $stmt->execute([
        'race_time' => $nextRaceTime->format('Y-m-d H:i:s'),
        'odds' => json_encode($odds)
    ]);

    return $pdo->lastInsertId();
}

// Hol das nächste oder laufende Rennen
function getCurrentRace($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM horse_races WHERE race_time > NOW() ORDER BY race_time ASC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Gewinner ermitteln
function pickWinner($odds) {
    $horses = array_keys($odds);
    $totalWeight = array_sum(array_map(fn($odd) => 1 / $odd, $odds));
    $random = mt_rand(0, $totalWeight * 1000) / 1000;
    $cumulative = 0;

    foreach ($odds as $horse => $odd) {
        $cumulative += 1 / $odd;
        if ($random <= $cumulative) {
            return $horse;
        }
    }
    return $horses[0]; // Fallback
}

// Aktuelles Rennen abrufen oder ein neues starten
$currentRace = getCurrentRace($pdo);

if (!$currentRace) {
    $raceId = startNewRace($pdo);
    $currentRace = $pdo->query("SELECT * FROM horse_races WHERE id = $raceId")->fetch(PDO::FETCH_ASSOC);
}

// JSON-Antwort für das Frontend
echo json_encode([
    'race_id' => $currentRace['id'],
    'race_time' => $currentRace['race_time'],
    'odds' => json_decode($currentRace['odds'], true)
]);
?>
