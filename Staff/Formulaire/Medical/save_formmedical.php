<?php
session_start();

if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    http_response_code(403);
    echo "Token CSRF invalide.";
    exit;
}

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

// Validation ID
if (!$id_joueur) {
    http_response_code(400);
    echo "ID joueur invalide.";
    exit;
}

// Gravité : entier entre 1 et 10
if (!preg_match('/^\d+$/', $gravite) || $gravite < 1 || $gravite > 10) {
    http_response_code(400);
    echo "Gravité invalide. Elle doit être un entier entre 1 et 10.";
    exit;
}

// Vérification de la longueur des champs texte
if (strlen($recommandation) > 500) {
    http_response_code(400);
    echo "La recommandation ne doit pas dépasser 500 caractères.";
    exit;
}

if (strlen($reprise) > 500) {
    http_response_code(400);
    echo "La reprise ne doit pas dépasser 500 caractères.";
    exit;
}

if (strpos($date, '/') !== false) {
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        $date = sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
    } else {
        http_response_code(400);
        echo "Format de date invalide.";
        exit;
    }
}


// Vérification que la date est bien une date valide
if (!strtotime($date)) {
    http_response_code(400);
    echo "Date invalide.";
    exit;
}

// Vérification que la date n’est pas dans le futur
if (strtotime($date) > strtotime(date('Y-m-d'))) {
    http_response_code(400);
    echo "La date ne peut pas être dans le futur.";
    exit;
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
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
    exit;
}
?>
