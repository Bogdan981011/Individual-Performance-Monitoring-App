<?php
session_start();


$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true); // true = tableau associatif

if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
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
        "INSERT INTO joueur (id_equipe, nom, prenom, email, mdp, annee, poste) 
        VALUES (:id_equipe, :nom, :prenom, :email, :mdp, :annee, :poste)");   
    
    $ids = [];

    // Préparation de la requête d'insertion
    foreach ($data['joueurs'] as $index => $joueur) {
        $nom    = trim($joueur['nom']);
        $prenom = trim($joueur['prenom']);
        $email  = trim($joueur['email']);
        $mdp    = trim($joueur['mdp']);
        $equipe = trim($joueur['equipe']); // nom de l’équipe
        $annee  = trim($joueur['annee']);
        $poste  = trim($joueur['poste']);

        if ($nom === '' || $prenom === '' || $email === '' || $equipe === '' || $mdp === '' || $poste === '' || $annee === '') {
            throw new Exception("Tous les champs sont obligatoires pour le joueur #".($index+1));
        }

        if (!empty($annee)) {
            $dateEntree = DateTime::createFromFormat('Y-m-d', $annee);
            if (!$dateEntree) {
                echo "Le format de l'année est invalide. '$annee'";
                exit;
            }

            $today = new DateTime();
            if ($dateEntree > $today) {
                echo "L'année ne peut pas être dans le futur.";
                exit;
            }
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
            ':mdp'       => password_hash($mdp, PASSWORD_DEFAULT),
            ':annee'     => $annee,
            ':poste'     => $poste
        ]);

        $ids[] = $pdo->lastInsertId();  
    }

    $pdo->commit();    
    echo json_encode([
        "status" => "ok",
        "ids" => $ids
    ]);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler si erreur
    http_response_code(500);
    echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    exit;
}
?> 