<?php
session_start();
require_once('../../bd.php'); // fichier de connexion à ta base

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $noms = $_POST['nom'] ?? [];
    $prenoms = $_POST['prenom'] ?? [];
    $emails = $_POST['email'] ?? [];
    $mdps = $_POST['mdp'] ?? [];
    $equipes = $_POST['equipe'] ?? [];
    $photos = $_FILES['photo'] ?? null;

    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    for ($i = 0; $i < count($noms); $i++) {
        $nom = htmlspecialchars($noms[$i]);
        $prenom = htmlspecialchars($prenoms[$i]);
        $email = htmlspecialchars($emails[$i]);
        $mdp = password_hash($mdps[$i], PASSWORD_DEFAULT);
        $equipe = htmlspecialchars($equipes[$i]);

        // Traitement du fichier photo
        $photo_path = "";
        if (isset($photos['name'][$i]) && $photos['error'][$i] == 0) {
            $tmp_name = $photos['tmp_name'][$i];
            $ext = pathinfo($photos['name'][$i], PATHINFO_EXTENSION);
            $filename = uniqid("photo_") . "." . $ext;
            $destination = $upload_dir . $filename;
            if (move_uploaded_file($tmp_name, $destination)) {
                $photo_path = $destination;
            }
        }

        // Insertion en base de données (exemple avec PDO)
        $stmt = $pdo->prepare("INSERT INTO joueur (nom, prenom, email, mdp, photo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $mdp, $photo_path]);
    }

    header("Location: joueur.php");
    exit();
} else {
    echo "Méthode non autorisée.";
}
?>
