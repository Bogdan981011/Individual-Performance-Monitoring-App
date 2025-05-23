<?php
session_start();

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

require_once '../../../bd.php';

$mesureTypes = ['taille', 'poids', 'img'];
$type = $data['joueurs'][0]['test'];

try {
    $pdo->beginTransaction(); // Démarrer la transaction

   if (in_array($type, $mesureTypes)) {
        // Préparer la requête pour la table `mesure`
        $stmt = $pdo->prepare("
            INSERT INTO mesure (id_joueur, date_mesure, type_mesure, valeur)
            VALUES (:id_joueur, :date_mesure, :type_mesure, :valeur)
        ");
    } else {
        // Préparer la requête pour la table `tests_physiques`
        $stmt = $pdo->prepare("
            INSERT INTO tests_physiques (id_joueur, date_test, type_test, mesure_test)
            VALUES (:id_joueur, :date_test, :type_test, :valeur)
        ");
    }
    
    foreach ($data['joueurs'] as $index => $joueur) {
        $id = filter_var($joueur['id_joueur'] ?? null, FILTER_VALIDATE_INT);
        $date = $joueur['date'] ?? '';
        $test = $joueur['test'] ?? '';
        $val = $joueur['val'] ?? null;

        // Vérif champs obligatoires
        if (!$id || $date === '' || $test === '') {
            throw new Exception("Tous les champs sont obligatoires pour le joueur #".($index+1));
        }

        // Vérif format date (YYYY-MM-DD) + non futur
        $dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRegex, $date)) {
            throw new Exception("Le format de la date est invalide pour le joueur #".($index+1));
        }
        $timestamp = strtotime($date);
        if ($timestamp === false || $timestamp > strtotime(date('Y-m-d'))) {
            throw new Exception("La date est invalide ou dans le futur pour le joueur #".($index+1));
        }

        // Vérif valeur numérique valide (ou vide autorisé)
        if ($val !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', $val)) {
            throw new Exception("La note doit être un nombre positif (max 2 décimales) pour le joueur #".($index+1));
        }


        if (in_array($type, $mesureTypes)) {
            $stmt->execute([
                'id_joueur' => $id,
                'date_mesure' => $date,
                'type_mesure' => $test,
                'valeur'    => $val
            ]);
        } else {
            $stmt->execute([
                'id_joueur' => $id,
                'date_test' => $date,
                'type_test' => $test,
                'valeur'    => $val
            ]);
        }
    }

    $pdo->commit(); // Valider la transaction
    echo "ok";

} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler si erreur
    http_response_code(500);
    echo "Erreur lors de l'enregistrement : " . $e->getMessage();
}
?>