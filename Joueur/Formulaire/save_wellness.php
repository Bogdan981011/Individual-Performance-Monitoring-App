<?php
session_start();

// Connexion à la base de données
include_once "../../bd.php";

// Vérification du token CSRF
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    http_response_code(403);
    echo "Token CSRF invalide.";
    exit;
}

// Vérification des champs requis
$required_fields = ['date', 'sommeil', 'courbatureHaut', 'courbatureBas', 'humeur'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        http_response_code(400);
        echo "Champ requis manquant : $field";
        exit;
    }
}

// Nettoyage et conversion des données
$date = trim($_POST['date']); // format : YYYY-MM-DD
$sommeil = (int) $_POST['sommeil'];
$courbatureHaut = (int) $_POST['courbatureHaut'];
$courbatureBas = (int) $_POST['courbatureBas'];
$humeur = (int) $_POST['humeur'];
$observations = trim($_POST['observations'] ?? '');

// Conversion date éventuelle (pas nécessaire si tu l’as bien en YYYY-MM-DD)
if (strpos($date, '/') !== false) {
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        // JJ/MM/AAAA → AAAA-MM-DD
        $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}

// Préparation et exécution de la requête
try {
    $stmt = $pdo->prepare("
        INSERT INTO wellness_form (id_joueur, date_form, sommeil, courbatures_haut, courbatures_bas, humeur, observations)
        VALUES (:id_joueur, :date, :sommeil, :courbatureHaut, :courbatureBas, :humeur, :observations)
    ");

    $stmt->execute([
        'id_joueur'      => $_SESSION['user_id'],
        'date'           => $date,
        'sommeil'        => $sommeil,
        'courbatureHaut' => $courbatureHaut,
        'courbatureBas'  => $courbatureBas,
        'humeur'         => $humeur,
        'observations'   => $observations
    ]);

    echo 'ok';

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
    exit;
}
?>
