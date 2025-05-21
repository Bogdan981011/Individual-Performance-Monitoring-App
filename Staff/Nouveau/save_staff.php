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

if (!isset($data['staffs']) || !is_array($data['staffs'])) {
    http_response_code(400);
    echo "Données invalides";
    exit;
}

require_once '../../bd.php';

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO staff (nom, prenom, email, role, mdp) VALUES (:nom, :prenom, :email, :poste, :mdp)");
        
    // Préparation de la requête d'insertion
    foreach ($data['staffs'] as $staff) {
        $nom = trim($staff['nom']);
        $prénom = trim($staff['prenom']);
        $email = trim($staff['email']);
        $poste = trim($staff['poste']);
        $mdp = trim($staff['mdp']);

        $stmt->execute([
            ':nom'       => $nom,
            ':prenom'    => $prénom,
            ':email'     => $email,
            ':poste'     => $poste,
            ':mdp'       => $mdp
        ]);
    }

    $pdo->commit();
    echo 'ok';

} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler si erreur
    http_response_code(500);
    echo "Erreur lors de l'enregistrement : " . $e->getMessage();
}
?>