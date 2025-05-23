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

if (!isset($data['joueurs']) || !is_array($data['joueurs'])) {
    http_response_code(400);
    echo "Données invalides";
    exit;
}

require_once '../../bd.php';

try {
    $pdo->beginTransaction();
    
    // Requête pour récupérer l'id_equipe depuis le nom
    $stmtEquipe = $pdo->prepare("SELECT id_equipe FROM equipe WHERE nom_equipe = :nom");
    
    // Requête d'insertion
    $stmtInsert = $pdo->prepare(
        "INSERT INTO joueur (id_equipe, nom, prenom, email, mdp) 
        VALUES (:id_equipe, :nom, :prenom, :email, :mdp)");   

    // Préparation de la requête d'insertion
    foreach ($data['joueurs'] as $index => $joueur) {
        $nom    = trim($joueur['nom']);
        $prenom = trim($joueur['prenom']);
        $email  = trim($joueur['email']);
        $mdp    = trim($joueur['mdp']);
        $equipe = trim($joueur['equipe']); // nom de l’équipe

        if ($nom === '' || $prenom === '' || $email === '' || $equipe === '' || $mdp === '') {
            throw new Exception("Tous les champs sont obligatoires pour le joueur #".($index+1));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format d'email invalide pour le joueur #".($index+1));
        }
        
        // Chercher l'id de l'équipe
        $stmtEquipe->execute([':nom' => $equipe]);
        $result = $stmtEquipe->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Équipe '$equipe' introuvable.");
        }

        $idEquipe = $result['id_equipe'];

        // Insérer le joueur
        $stmtInsert->execute([
            ':id_equipe' => $idEquipe,
            ':nom'       => $nom,
            ':prenom'    => $prenom,
            ':email'     => $email,
            ':mdp'       => password_hash($mdp, PASSWORD_DEFAULT)
        ]);

    }

    $pdo->commit();    
    echo "ok";
    exit;

} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler si erreur
    http_response_code(500);
    echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    exit;
}
?> 