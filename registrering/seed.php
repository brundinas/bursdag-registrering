<?php

require __DIR__ . '/db.php';

$pdo = db();

if ($pdo->query('SELECT COUNT(*) FROM aktiviteter')->fetchColumn() > 0) {
    exit("Aktiviteter finnes allerede.\n");
}

$stmt = $pdo->prepare('INSERT INTO aktiviteter (aktivitet, beskrivelse) VALUES (?, ?)');
foreach (array_slice($argv, 1) as $dato) {
    $stmt->execute([$dato, null]);
}

echo "Ferdig.\n";
