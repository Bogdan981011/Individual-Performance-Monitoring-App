<?php
session_start();

// Lire le corps brut de la requête JSON
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true); // true = tableau associatif

if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
    // Si le CSRF token ne correspond pas, on renvoie une erreur
    http_response_code(403); // Forbidden
    echo "Erreur CSRF : Token invalide";
    exit;
}

if (!isset($data['joueurs']) || !is_array($data['joueurs'])) {
    http_response_code(400);
    echo "Données invalides";
    exit;
}

include_once "../../../bd.php";

try {
    $pdo->beginTransaction(); // On démarre la transaction

    $stmt = $pdo->prepare("
       INSERT INTO tests_fonctionnels 
        (id_joueur, date_test, squat_arrache, iso_leg_curl, souplesse_chaine_post, flamant_rose, souplesse_membres_supérieurs) 
        VALUES (:id, :date, :squat, :iso, :souplesse, :flamant, :haut)
    ");

    foreach ($data['joueurs'] as $joueur) {
        $id = filter_var($joueur['id_joueur'] ?? null, FILTER_VALIDATE_INT);
        $date = $joueur['date'] ?? '';
        $squat = strtoupper(trim($joueur['squat'] ?? ''));
        $iso = strtoupper(trim($joueur['iso'] ?? ''));
        $souplesse = strtoupper(trim($joueur['souplesse'] ?? ''));
        $flamant = strtoupper(trim($joueur['flamant'] ?? ''));
        $haut = strtoupper(trim($joueur['haut'] ?? ''));

        if (!$id || !$date) continue;

        $stmt->execute([
            'id' => $id,
            'date' => $date,
            'squat' => $squat,
            'iso' => $iso,
            'souplesse' => $souplesse,
            'flamant' => $flamant,
            'haut' => $haut
        ]);
    }    

    $pdo->commit(); // Valider la transaction
    echo "ok";
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur BDD : " . $e->getMessage();
    exit;
}