<?php

// Connexion à la base de données
include_once "../../../bd.php";

// Vérifier que les données nécessaires sont présentes
$required_fields = ['type', 'gravite', 'date', 'id_joueur'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo "Champ requis manquant : $field";
        exit;
    }
}

// Nettoyer les données
$type = trim($_POST['type']);
$gravite = (int) $_POST['gravite'];
$date = trim($_POST['date']); // format attendu : JJ/MM/AAAA ou YYYY-MM-DD
$recommandation = trim($_POST['recommandation'] ?? '');
$reprise = trim($_POST['reprise'] ?? '');
$id_joueur = (int) $_POST['id_joueur'];

// Conversion de date si nécessaire (si elle arrive au format JJ/MM/AAAA)
if (strpos($date, '/') !== false) {
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        // JJ/MM/AAAA → AAAA-MM-DD
        $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}

// Préparer la requête d'insertion
try {
    $stmt = $pdo->prepare("
        INSERT INTO medical_form (id_joueur, type_blessure, gravite, date_blessure, recommandation, reprise)
        VALUES (:id_joueur, :type, :gravite, :date, :recommandation, :reprise)
    ");

    $stmt->execute([
        'id_joueur'     => $id_joueur,
        'type'          => $type,
        'gravite'       => $gravite,
        'date'          => $date,
        'recommandation'=> $recommandation,
        'reprise'       => $reprise
    ]);

    // Redirection après succès
    echo 'ok';

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
    exit;
}
?>
