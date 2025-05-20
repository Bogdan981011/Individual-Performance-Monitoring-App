<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT test_type, note, date_test FROM tests WHERE joueur_id = ? ORDER BY test_type, date_test");
$stmt->execute([$user_id]);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regrouper les données par type
$grouped = [];
foreach ($tests as $test) {
    $type = $test['test_type'];
    $grouped[$type]['labels'][] = $test['date_test'];
    $grouped[$type]['data'][] = $test['note'];
}

header('Content-Type: application/json');
echo json_encode($grouped);
