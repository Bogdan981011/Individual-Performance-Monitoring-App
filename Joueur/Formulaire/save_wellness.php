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

// Validation de la date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
    http_response_code(400);
    echo "Format de date invalide.";
    exit;
}
if (strtotime($date) > strtotime(date('Y-m-d'))) {
    http_response_code(400);
    echo "La date ne peut pas être dans le futur.";
    exit;
}

// Validation des scores
if ($sommeil < 1 || $sommeil > 5) {
    http_response_code(400);
    echo "Le score de sommeil doit être entre 1 et 5.";
    exit;
}
if ($courbatureHaut < 0 || $courbatureHaut > 10) {
    http_response_code(400);
    echo "Le score de courbature (haut) doit être entre 0 et 10.";
    exit;
}
if ($courbatureBas < 0 || $courbatureBas > 10) {
    http_response_code(400);
    echo "Le score de courbature (bas) doit être entre 0 et 10.";
    exit;
}
if ($humeur < 1 || $humeur > 5) {
    http_response_code(400);
    echo "Le score d'humeur doit être entre 1 et 5.";
    exit;
}

// Validation des observations
if (strlen($observations) > 500) {
    http_response_code(400);
    echo "Le champ 'observations' ne doit pas dépasser 500 caractères.";
    exit;
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
