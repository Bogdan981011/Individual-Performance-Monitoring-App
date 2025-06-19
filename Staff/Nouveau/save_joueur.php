<?php
session_start();

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo "Erreur CSRF : Token invalide";
    exit;
}

if (!isset($_POST['nom']) || !is_array($_POST['nom'])) {
    http_response_code(400);
    echo "Données invalides";
    exit;
}

require_once '../../bd.php';

try {
    $pdo->beginTransaction();

    // Préparer les requêtes
    $stmtEquipe = $pdo->prepare("SELECT id_equipe FROM equipe WHERE nom_equipe = :nom");
    $stmtInsert = $pdo->prepare(
        "INSERT INTO joueur (id_equipe, nom, prenom, email, mdp, photo) 
        VALUES (:id_equipe, :nom, :prenom, :email, :mdp, :photo)"
    );

    // Dossier d'upload (à adapter selon ta structure)
    $uploadDir = __DIR__ . '/uploads/joueurs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $joueursCount = count($_POST['nom']);

    for ($i = 0; $i < $joueursCount; $i++) {
        $nom    = trim($_POST['nom'][$i]);
        $prenom = trim($_POST['prenom'][$i]);
        $email  = trim($_POST['mail'][$i]);
        $mdp    = trim($_POST['mdp'][$i]);
        $equipe = trim($_POST['equipe'][$i]);

        if ($nom === '' || $prenom === '' || $email === '' || $equipe === '' || $mdp === '') {
            throw new Exception("Tous les champs sont obligatoires pour le joueur #" . ($i + 1));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format d'email invalide pour le joueur #" . ($i + 1));
        }

        // Récupérer l'id de l'équipe
        $stmtEquipe->execute([':nom' => $equipe]);
        $result = $stmtEquipe->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("Équipe '$equipe' introuvable.");
        }
        $idEquipe = $result['id_equipe'];

        // Gestion de la photo
        $photoPath = null;
        if (isset($_FILES['photo']['error'][$i]) && $_FILES['photo']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['photo']['tmp_name'][$i];
            $mime = mime_content_type($tmpName);
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowed, true)) {
                throw new Exception("Format de photo invalide pour le joueur #" . ($i + 1));
            }

            $ext = match($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'bin'
            };

            $fileName = uniqid('joueur_') . '.' . $ext;
            $destination = $uploadDir . $fileName;
            if (!move_uploaded_file($tmpName, $destination)) {
                throw new Exception("Erreur lors de l'upload de la photo pour le joueur #" . ($i + 1));
            }
            // Stocker chemin relatif à la racine du projet (adapter si besoin)
            $photoPath = 'uploads/joueurs/' . $fileName;
        }

        // Insertion en base
        $stmtInsert->execute([
            ':id_equipe' => $idEquipe,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mdp' => password_hash($mdp, PASSWORD_DEFAULT),
            ':photo' => $photoPath
        ]);
    }

    $pdo->commit();
    echo "ok";
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    exit;
}
?>
