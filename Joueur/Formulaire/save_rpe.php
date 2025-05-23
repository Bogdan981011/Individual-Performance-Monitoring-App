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

// Vérifier que les données nécessaires sont présentes
$required_fields = ['date','type_entrainement','temps_entrainement' ,'difficulte', 'observations', 'id_joueur'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo "Champ requis manquant : $field";
        exit;
    }
}

// Nettoyer les données
$date = trim($_POST['date']); // format attendu : JJ/MM/AAAA ou YYYY-MM-DD
$type_entrainement = trim($_POST['type_entrainement']);
$temps_entrainement = trim($_POST['temps_entrainement']);
$difficulte = (int) $_POST['difficulte'];
$observations = trim($_POST['observations']);
$id_joueur = (int) $_POST['id_joueur'];

// Vérification de la date
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

// Vérification du RPE
if ($difficulte < 1 || $difficulte > 10) {
    http_response_code(400);
    echo "La difficulté (RPE) doit être entre 1 et 10.";
    exit;
}

// Longueur du commentaire
if (strlen($observations) > 500) {
    http_response_code(400);
    echo "Le commentaire ne doit pas dépasser 500 caractères.";
    exit;
}


try {
    $stmt = $pdo->prepare("
        INSERT INTO rpe_form (id_joueur, date_form, temps_entrainement, type_entrainement, difficulte, observations)
        VALUES (:id_joueur, :date, :temps_entrainement,:type_entrainement ,:difficulte, :observations)
    ");

    $stmt->execute([
        'id_joueur'        => $_SESSION['user_id'],
        'date'           => $date,
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
