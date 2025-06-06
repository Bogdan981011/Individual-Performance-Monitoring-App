<?php
session_start();

if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    http_response_code(403);
    echo "Token CSRF invalide.";
    exit;
}

include_once "../../bd.php";


// Nettoyer les données
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$poste = $_POST['poste'] ?? '';
$email = $_POST['email'] ?? '';
$mdp = $_POST['mdp'] ?? '';
$annee = $_POST['annee'] ?? '';
$nom_equipe = $_POST['nom_equipe'] ?? '';
$id_joueur = $_POST['id_joueur'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';


// Validation de l'email
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Le format de l'email est invalide.";
    exit;
}

// Validation de l'année (doit être au format JJ/MM/AAAA et ne pas être dans le futur)
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


// Préparer la requête d'insertion conditionnelle
try {    
    // Requête pour récupérer l'id_equipe depuis le nom
    $stmtEquipe = $pdo->prepare("SELECT id_equipe FROM equipe WHERE nom_equipe = :nom");
    
    // Chercher l'id de l'équipe
    $stmtEquipe->execute([':nom' => $nom_equipe]);
    $result = $stmtEquipe->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("Équipe '$nom_equipe' introuvable.");
    }

    $id_equipe = $result['id_equipe'];

    if (empty($mdp)) {
        // Si le mot de passe est vide, ne pas mettre à jour le mot de passe
        $stmt = $pdo->prepare("
            UPDATE joueur 
            SET 
                nom = :nom,
                prenom = :prenom,
                id_equipe = :id_eq,
                poste = :poste,
                email = :email,
                annee = :annee
            WHERE id_joueur = :id_joueur
        ");
        $stmt->execute([
            'id_joueur' => $id_joueur,
            'nom' => $nom,
            'prenom' => $prenom,
            'id_eq' => $id_equipe,
            'poste' => $poste,
            'email' => $email,
            'annee' => $annee
        ]);
    } else {
        // Si le mot de passe n'est pas vide, mettre à jour tous les champs, y compris le mot de passe
        $stmt = $pdo->prepare("
            UPDATE joueur 
            SET 
                nom = :nom,
                prenom = :prenom,
                id_equipe = :id_eq,
                poste = :poste,
                email = :email,
                mdp = :mdp,
                annee = :annee
            WHERE id_joueur = :id_joueur
        ");
        $stmt->execute([
            'id_joueur' => $id_joueur,
            'nom' => $nom,
            'prenom' => $prenom,
            'id_eq' => $id_equipe,
            'poste' => $poste,
            'email' => $email,
            'mdp' => password_hash($mdp, PASSWORD_BCRYPT), // Hash du mot de passe pour sécurité
            'annee' => $annee
        ]);
    }

    echo 'ok';

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
    exit;
}

?>