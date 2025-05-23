<?php
session_start();

// Vérifie le CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo "Token CSRF invalide.";
    exit;
}

// Connexion à la base
require_once "../../bd.php"; 

// Récupération des données
$idJoueur = $_SESSION['user_id'];
$ancienMdp = $_POST['mdp_av'] ?? '';
$nouveauMdp = $_POST['new_mdp'] ?? '';

// Validation simple
if (empty($ancienMdp) || empty($nouveauMdp)) {
    echo "Champs manquants.";
    exit;
}

try {
    // Récupération du mot de passe actuel hashé
    $stmt = $pdo->prepare("SELECT mdp FROM joueur WHERE id_joueur = :id");
    $stmt->execute([':id' => $idJoueur]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "Utilisateur introuvable.";
        exit;
    }

    $hashActuel = $row['mdp'];

    // Vérifie que l'ancien mot de passe correspond
    if (!password_verify($ancienMdp, $hashActuel)) {
        echo "Ancien mot de passe incorrect.";
        exit;
    }

    // Met à jour avec le nouveau mot de passe hashé
    $newHash = password_hash($nouveauMdp, PASSWORD_BCRYPT);
    $updateStmt = $pdo->prepare("UPDATE joueur SET mdp = :newMdp WHERE id_joueur = :id");
    $updateStmt->execute([
        ':newMdp' => $newHash,
        ':id' => $idJoueur
    ]);

    echo "ok";

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
}
