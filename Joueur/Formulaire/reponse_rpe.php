<?php
session_start();
// Connexion à la base de données
include_once "../../bd.php";

// Vérifier que les données nécessaires sont présentes
$required_fields = ['type_entrainement','temps_entrainement' ,'difficulte', 'observations', 'id_joueur'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo "Champ requis manquant : $field";
        exit;
    }
}

// Nettoyer les données
$type_entrainement = trim($_POST['type_entrainement']);
$temps_entrainement = trim($_POST['temps_entrainement']);
$difficulte = (int) $_POST['difficulte'];
$observations = trim($_POST['observations']);
$id_joueur = (int) $_POST['id_joueur'];



// Date du jour pour la colonne date_form
$date_form = date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        INSERT INTO rpe_form (id_joueur,temps_entrainement, type_entrainement, difficulte, observations)
        VALUES (:id_joueur, :temps_entrainement,:type_entrainement ,:difficulte, :observations)
    ");

    $stmt->execute([
        'id_joueur'        => $_SESSION['user_id'],
        'type_entrainement'=> $type_entrainement,
        'temps_entrainement' => $temps_entrainement,
        'difficulte'       => $difficulte,
        'observations'     => $observations
    ]);

    echo "ok";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
    exit;
}
?>
