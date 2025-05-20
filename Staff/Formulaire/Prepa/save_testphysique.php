<?php
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true); // true = tableau associatif

require_once '../../../bd.php';

if (!isset($data['joueurs']) || !is_array($data['joueurs'])) {
    http_response_code(400);
    echo "Données invalides";
    exit;
}

try {
    $pdo->beginTransaction(); // Démarrer la transaction
    echo 'p';

    $stmt = $pdo->prepare("
        INSERT INTO tests_physiques (id_joueur, date_test, type_test, mesure_test)
        VALUES (:id_joueur, :date_test, :type_test, :valeur)
    ");

    foreach ($data['joueurs'] as $joueur) {
        $id = filter_var($joueur['id_joueur'] ?? null, FILTER_VALIDATE_INT);
        $date = $joueur['date'] ?? '';
        $test = $joueur['test'] ?? '';
        $val = $joueur['val'] ?? '';
        
        if (!$id || !$date || !$test) continue;

        $stmt->execute([
            'id_joueur' => $id
            'date_test' => $date,
            'type_test' => $tes$,
            'valeur'    => $val
        ]);
    }

    $pdo->commit(); // Valider la transaction
    echo "";

} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler si erreur
    http_response_code(500);
    echo "Erreur lors de l'enregistrement : " . $e->getMessage();
}