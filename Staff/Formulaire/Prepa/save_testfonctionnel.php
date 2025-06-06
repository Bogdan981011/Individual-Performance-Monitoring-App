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

    foreach ($data['joueurs'] as $index => $joueur) {
        $id = filter_var($joueur['id_joueur'] ?? null, FILTER_VALIDATE_INT);
        $date = $joueur['date'] ?? '';
        $fields = ['squat', 'iso', 'souplesse', 'flamant', 'haut'];
        $valeurs = [];

        if (!$id || !$date) {
            throw new Exception("Tous les champs sont obligatoires pour le joueur #".($index+1));
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) > strtotime(date('Y-m-d'))) {
            throw new Exception("Date invalide ou dans le futur pour le joueur #".($index+1));
        }

        foreach ($fields as $champ) {
            $val = strtoupper(trim($joueur[$champ] ?? ''));

            if ($val !== '' && !in_array($val, ['A', 'EA', 'NA'])) {
                throw new Exception("Valeur invalide pour '$champ' du joueur #".($index+1)." (seuls A, EA ou NA sont autorisés)");
            }

            $valeurs[$champ] = $val ?: null;
        }

        $stmt->execute([
            'id' => $id,
            'date' => $date,
            'squat' => $valeurs['squat'],
            'iso' => $valeurs['iso'],
            'souplesse' => $valeurs['souplesse'],
            'flamant' => $valeurs['flamant'],
            'haut' => $valeurs['haut']
        ]);
    }   

    $pdo->commit(); // Valider la transaction
    echo "ok";
    exit;
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur BDD : " . $e->getMessage();
    exit;
}