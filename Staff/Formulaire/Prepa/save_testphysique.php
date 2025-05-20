<?php
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true); // true = tableau associatif

require_once '../../../bd.php';

if (!isset($data['joueurs']) || !is_array($data['joueurs'])) {
    http_response_code(400);
    echo "Données invalides";
    exit;
}

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
    
    foreach ($data['joueurs'] as $joueur) {
        $id = filter_var($joueur['id_joueur'] ?? null, FILTER_VALIDATE_INT);
        $date = $joueur['date'] ?? '';
        $test = $joueur['test'] ?? '';
        $val = $joueur['val'] ?? null;

        if ($val === '' || !is_numeric($val)) {
            $val = null;
        }
        
        if (!$id || !$date || !$test) continue;

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